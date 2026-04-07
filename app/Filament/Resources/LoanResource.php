<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LoanResource\Pages;
use App\Models\Loan;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class LoanResource extends Resource
{
    protected static ?string $model = Loan::class;
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Prêts';
    protected static ?string $navigationLabel = 'Prêts';
    protected static ?string $modelLabel = 'Prêt';
    protected static ?string $pluralModelLabel = 'Prêts';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Demande de Prêt')
                ->schema([
                    Forms\Components\Select::make('member_id')
                        ->label('Membre')
                        ->relationship('member', 'member_number')
                        ->getOptionLabelFromRecordUsing(fn($record) => "{$record->member_number} — {$record->user->name}")
                        ->searchable()
                        ->preload()
                        ->required(),
                    Forms\Components\Select::make('status')
                        ->label('Statut')
                        ->options([
                            'pending' => 'En attente',
                            'active' => 'En cours',
                            'repaid' => 'Remboursé',
                            'defaulted' => 'En défaut',
                            'rejected' => 'Rejeté',
                        ])
                        ->default('pending')
                        ->required(),
                ])->columns(2),

            Forms\Components\Section::make('Calculateur de Prêt')
                ->description('Remplissez les champs pour calculer automatiquement le montant total.')
                ->schema([
                    Forms\Components\TextInput::make('principal_amount')
                        ->label('Capital demandé (HTG)')
                        ->numeric()
                        ->required()
                        ->minValue(1)
                        ->live(debounce: 500)
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            self::recalculate($get, $set);
                        }),
                    Forms\Components\TextInput::make('interest_rate')
                        ->label('Taux d\'intérêt (%)')
                        ->numeric()
                        ->default(fn() => Setting::get('default_loan_interest_rate', 10))
                        ->minValue(0)
                        ->live(debounce: 500)
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            self::recalculate($get, $set);
                        }),
                    Forms\Components\TextInput::make('term_months')
                        ->label('Durée (mois)')
                        ->numeric()
                        ->default(12)
                        ->minValue(1)
                        ->live(debounce: 500)
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            self::recalculate($get, $set);
                        }),
                    Forms\Components\Placeholder::make('total_display')
                        ->label('💰 Total à rembourser (HTG)')
                        ->content(function (Get $get): string {
                            $principal = floatval($get('principal_amount') ?? 0);
                            $rate = floatval($get('interest_rate') ?? 0);
                            $months = intval($get('term_months') ?? 0);
                            $total = $principal + ($principal * ($rate / 100) * ($months / 12));
                            return number_format($total, 2) . ' HTG';
                        }),
                    Forms\Components\Placeholder::make('monthly_payment')
                        ->label('📅 Mensualité estimée (HTG)')
                        ->content(function (Get $get): string {
                            $principal = floatval($get('principal_amount') ?? 0);
                            $rate = floatval($get('interest_rate') ?? 0);
                            $months = intval($get('term_months') ?? 1);
                            if ($months <= 0) return '–';
                            $total = $principal + ($principal * ($rate / 100) * ($months / 12));
                            return number_format($total / $months, 2) . ' HTG / mois';
                        }),
                ])->columns(2),

            Forms\Components\Section::make('Dates')
                ->schema([
                    Forms\Components\DatePicker::make('disbursement_date')->label('Date de décaissement'),
                    Forms\Components\DatePicker::make('due_date')->label('Date d\'échéance finale'),
                ])->columns(2),

            // Hidden computed fields
            Forms\Components\Hidden::make('total_to_repay'),
            Forms\Components\Hidden::make('balance_remaining'),
        ]);
    }

    protected static function recalculate(Get $get, Set $set): void
    {
        $principal = floatval($get('principal_amount') ?? 0);
        $rate = floatval($get('interest_rate') ?? 0);
        $months = intval($get('term_months') ?? 0);
        $total = $principal + ($principal * ($rate / 100) * ($months / 12));
        $set('total_to_repay', round($total, 2));
        $set('balance_remaining', round($total, 2));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('member.user.name')->label('Membre')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('principal_amount')->label('Capital')->money('HTG'),
                Tables\Columns\TextColumn::make('interest_rate')->label('Taux')->suffix('%'),
                Tables\Columns\TextColumn::make('term_months')->label('Durée')->suffix(' mois'),
                Tables\Columns\TextColumn::make('total_to_repay')->label('Total')->money('HTG'),
                Tables\Columns\TextColumn::make('balance_remaining')->label('Restant')->money('HTG'),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Statut')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'active',
                        'gray' => 'repaid',
                        'danger' => 'defaulted',
                        'info' => 'rejected',
                    ])
                    ->formatStateUsing(fn($state) => match($state) {
                        'pending' => 'En attente',
                        'active' => 'En cours',
                        'repaid' => 'Remboursé',
                        'defaulted' => 'En défaut',
                        'rejected' => 'Rejeté',
                        default => $state,
                    }),
                // Progress bar for repayment
                Tables\Columns\TextColumn::make('repayment_progress')
                    ->label('Progression')
                    ->getStateUsing(function ($record) {
                        if ($record->total_to_repay <= 0) return '—';
                        $paid = $record->total_to_repay - $record->balance_remaining;
                        $pct = round(($paid / $record->total_to_repay) * 100);
                        return "{$pct}%";
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'En attente', 'active' => 'En cours',
                        'repaid' => 'Remboursé', 'defaulted' => 'En défaut',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Approuver')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn($record) => $record->status === 'pending')
                    ->action(fn($record) => $record->update(['status' => 'active', 'disbursement_date' => now()])),
                Tables\Actions\Action::make('reject')
                    ->label('Rejeter')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn($record) => $record->status === 'pending')
                    ->action(fn($record) => $record->update(['status' => 'rejected'])),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Détails du Prêt')
                ->schema([
                    Infolists\Components\TextEntry::make('member.user.name')->label('Membre'),
                    Infolists\Components\TextEntry::make('principal_amount')->label('Capital')->money('HTG'),
                    Infolists\Components\TextEntry::make('interest_rate')->label('Taux')->suffix('%'),
                    Infolists\Components\TextEntry::make('term_months')->label('Durée')->suffix(' mois'),
                    Infolists\Components\TextEntry::make('total_to_repay')->label('Total à rembourser')->money('HTG'),
                    Infolists\Components\TextEntry::make('balance_remaining')->label('Solde restant')->money('HTG'),
                    Infolists\Components\TextEntry::make('status')->label('Statut')->badge(),
                    Infolists\Components\TextEntry::make('disbursement_date')->label('Date de décaissement')->date('d/m/Y'),
                    Infolists\Components\TextEntry::make('due_date')->label('Échéance')->date('d/m/Y'),
                ])->columns(3),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            LoanResource\RelationManagers\RepaymentsRelationManager::class,
            LoanResource\RelationManagers\SchedulesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLoans::route('/'),
            'create' => Pages\CreateLoan::route('/create'),
            'view' => Pages\ViewLoan::route('/{record}'),
            'edit' => Pages\EditLoan::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $pending = static::getModel()::where('status', 'pending')->count();
        return $pending > 0 ? (string)$pending : null;
    }

    public static function getNavigationBadgeColor(): ?string { return 'warning'; }
}
