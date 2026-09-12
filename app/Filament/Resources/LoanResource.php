<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LoanResource\Pages;
use App\Models\Loan;
use App\Models\Setting;
use App\Services\FundService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

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
                        ->getOptionLabelFromRecordUsing(fn($record) => "{$record->member_number} — {$record->full_name}")
                        ->searchable()
                        ->preload()
                        ->required()
                        ->helperText(fn() => '💰 Fonds disponible : ' . number_format(app(FundService::class)->getAvailableBalance(), 2) . ' $'),

                    Forms\Components\Select::make('status')
                        ->label('Statut')
                        ->options([
                            'pending'   => 'En attente',
                            'active'    => 'En cours',
                            'repaid'    => 'Remboursé',
                            'defaulted' => 'En défaut',
                            'rejected'  => 'Rejeté',
                        ])
                        ->default('pending')
                        ->required(),
                ])->columns(2),

            Forms\Components\Section::make('Calculateur de Prêt')
                ->description('Le montant total et la mensualité sont calculés automatiquement en intérêt simple.')
                ->schema([
                    Forms\Components\TextInput::make('principal_amount')
                        ->label('Capital demandé ($)')
                        ->numeric()
                        ->required()
                        ->minValue(1)
                        ->live(debounce: 500)
                        ->afterStateUpdated(fn(Get $get, Set $set) => self::recalculate($get, $set)),

                    Forms\Components\TextInput::make('interest_rate')
                        ->label('Taux d\'intérêt (%)')
                        ->numeric()
                        ->default(fn() => Setting::get('default_loan_interest_rate', 10))
                        ->minValue(0)
                        ->live(debounce: 500)
                        ->afterStateUpdated(fn(Get $get, Set $set) => self::recalculate($get, $set)),

                    Forms\Components\TextInput::make('term_months')
                        ->label('Durée (mois)')
                        ->numeric()
                        ->default(12)
                        ->minValue(1)
                        ->live(debounce: 500)
                        ->afterStateUpdated(fn(Get $get, Set $set) => self::recalculate($get, $set)),

                    Forms\Components\Placeholder::make('fund_check')
                        ->label('✅ Vérification des Fonds')
                        ->content(function (Get $get): string {
                            $amount    = floatval($get('principal_amount') ?? 0);
                            $service   = app(FundService::class);
                            $available = $service->getAvailableBalance();
                            if ($amount <= 0) return "Saisissez un montant pour vérifier.";
                            if ($service->canApprove($amount, 'loan')) {
                                return "✅ Fonds suffisants — Disponible : " . number_format($available, 2) . " $";
                            }
                            return "❌ " . $service->getInsufficientFundsMessage($amount, 'loan');
                        }),

                    Forms\Components\Placeholder::make('total_display')
                        ->label('💰 Total à rembourser ($)')
                        ->content(function (Get $get): string {
                            $principal = floatval($get('principal_amount') ?? 0);
                            $rate      = floatval($get('interest_rate') ?? 0);
                            $months    = intval($get('term_months') ?? 0);
                            $total     = $principal + ($principal * ($rate / 100) * ($months / 12));
                            return number_format($total, 2) . ' $';
                        }),

                    Forms\Components\Placeholder::make('monthly_payment')
                        ->label('📅 Mensualité estimée ($)')
                        ->content(function (Get $get): string {
                            $principal = floatval($get('principal_amount') ?? 0);
                            $rate      = floatval($get('interest_rate') ?? 0);
                            $months    = intval($get('term_months') ?? 1);
                            if ($months <= 0) return '–';
                            $total = $principal + ($principal * ($rate / 100) * ($months / 12));
                            return number_format($total / $months, 2) . ' $ / mois';
                        }),
                ])->columns(3),

            Forms\Components\Section::make('Dates')
                ->schema([
                    Forms\Components\DatePicker::make('disbursement_date')
                        ->label('Date de décaissement')
                        ->live()
                        ->afterStateUpdated(fn(Get $get, Set $set) => self::recalculate($get, $set)),
                    
                    Forms\Components\DatePicker::make('due_date')
                        ->label('Date d\'échéance finale')
                        ->readonly()
                        ->helperText('Calculée automatiquement selon la durée.'),
                ])->columns(2),

            Forms\Components\Hidden::make('total_to_repay'),
            Forms\Components\Hidden::make('balance_remaining'),
        ]);
    }

    protected static function recalculate(Get $get, Set $set): void
    {
        $principal = floatval($get('principal_amount') ?? 0);
        $rate      = floatval($get('interest_rate') ?? 0);
        $months    = intval($get('term_months') ?? 0);
        
        // Calcul financier
        $total     = $principal + ($principal * ($rate / 100) * ($months / 12));
        $set('total_to_repay', round($total, 2));
        $set('balance_remaining', round($total, 2));

        // Calcul de la date d'échéance
        $startDate = $get('disbursement_date');
        if ($startDate && $months > 0) {
            $set('due_date', \Carbon\Carbon::parse($startDate)->addMonths($months)->format('Y-m-d'));
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('member.full_name')
                    ->label('Membre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('principal_amount')->label('Capital')->numeric(decimalPlaces: 2)->suffix(' ' . \App\Models\Setting::get('currency', 'Gourdes')),
                Tables\Columns\TextColumn::make('interest_rate')->label('Taux')->suffix('%'),
                Tables\Columns\TextColumn::make('term_months')->label('Durée')->suffix(' mois'),
                Tables\Columns\TextColumn::make('total_to_repay')->label('Total')->numeric(decimalPlaces: 2)->suffix(' ' . \App\Models\Setting::get('currency', 'Gourdes')),
                Tables\Columns\TextColumn::make('balance_remaining')->label('Restant')->numeric(decimalPlaces: 2)->suffix(' ' . \App\Models\Setting::get('currency', 'Gourdes'))
                    ->color(fn($state) => $state > 0 ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('repayment_progress')
                    ->label('Progression')
                    ->getStateUsing(function ($record) {
                        if ($record->total_to_repay <= 0) return '—';
                        $paid = $record->total_to_repay - $record->balance_remaining;
                        $pct  = round(($paid / $record->total_to_repay) * 100);
                        return "{$pct}%";
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'pending'   => 'warning',
                        'active'    => 'success',
                        'repaid'    => 'gray',
                        'defaulted' => 'danger',
                        'rejected'  => 'info',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn($state) => match($state) {
                        'pending'   => 'En attente',
                        'active'    => 'En cours',
                        'repaid'    => 'Remboursé',
                        'defaulted' => 'En défaut',
                        'rejected'  => 'Rejeté',
                        default     => $state,
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending'   => 'En attente',
                        'active'    => 'En cours',
                        'repaid'    => 'Remboursé',
                        'defaulted' => 'En défaut',
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
                    ->action(function ($record) {
                        $service = app(FundService::class);

                        if (!$service->canApprove($record->principal_amount, 'loan')) {
                            Notification::make()
                                ->title('Fonds insuffisants')
                                ->body($service->getInsufficientFundsMessage($record->principal_amount, 'loan'))
                                ->danger()
                                ->send();
                            return;
                        }

                        $record->update(['status' => 'active', 'disbursement_date' => now()]);

                        // Générer automatiquement l'échéancier de remboursement
                        $record->generateSchedules();

                        // Enregistrer la sortie dans le journal de caisse
                        $service->logMovement(
                            'outflow',
                            $record->principal_amount,
                            "Décaissement prêt #{$record->id} — {$record->member->full_name}",
                            $record
                        );

                        Notification::make()->title('Prêt approuvé et décaissé')->success()->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Rejeter')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn($record) => $record->status === 'pending')
                    ->action(function ($record) {
                        $record->update(['status' => 'rejected']);
                        Notification::make()->title('Prêt rejeté')->danger()->send();
                    }),

                Tables\Actions\Action::make('add_repayment')
                    ->label('Rembourser')
                    ->icon('heroicon-o-banknotes')
                    ->color('info')
                    ->visible(fn($record) => $record->status === 'active')
                    ->form([
                        Forms\Components\Placeholder::make('info')
                            ->label('Solde restant')
                            ->content(fn($record) => number_format($record->balance_remaining, 2) . ' ' . Setting::get('currency', 'Gourdes')),
                        Forms\Components\TextInput::make('amount_paid')
                            ->label('Montant remboursé (' . Setting::get('currency', 'Gourdes') . ')')
                            ->numeric()
                            ->required()
                            ->minValue(1),
                        Forms\Components\DatePicker::make('payment_date')
                            ->label('Date du paiement')
                            ->default(now())
                            ->required(),
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->rows(2),
                    ])
                    ->action(function ($record, array $data) {
                        $repayment = $record->repayments()->create([
                            'amount_paid'  => $data['amount_paid'],
                            'payment_date' => $data['payment_date'],
                            'notes'        => $data['notes'] ?? null,
                        ]);

                        // Note: Le solde du prêt et son statut sont mis à jour automatiquement 
                        // par le "boot method" du modèle LoanRepayment lors de la création.

                        // Enregistrer l'entrée dans la caisse
                        app(FundService::class)->logMovement(
                            'inflow',
                            $data['amount_paid'],
                            "Remboursement prêt #{$record->id} — {$record->member->full_name}",
                            $repayment
                        );

                        Notification::make()
                            ->title('Remboursement enregistré')
                            ->body('Le solde du prêt a été mis à jour.')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Détails du Prêt')
                ->schema([
                    Infolists\Components\TextEntry::make('member.full_name')->label('Membre'),
                    Infolists\Components\TextEntry::make('principal_amount')->label('Capital')->numeric(decimalPlaces: 2)->suffix(' ' . \App\Models\Setting::get('currency', 'Gourdes')),
                    Infolists\Components\TextEntry::make('interest_rate')->label('Taux d\'intérêt')->suffix('%'),
                    Infolists\Components\TextEntry::make('term_months')->label('Durée')->suffix(' mois'),
                    Infolists\Components\TextEntry::make('total_to_repay')->label('Total à rembourser')->numeric(decimalPlaces: 2)->suffix(' ' . \App\Models\Setting::get('currency', 'Gourdes')),
                    Infolists\Components\TextEntry::make('balance_remaining')->label('Solde restant')->numeric(decimalPlaces: 2)->suffix(' ' . \App\Models\Setting::get('currency', 'Gourdes')),
                    Infolists\Components\TextEntry::make('status')->label('Statut')->badge(),
                    Infolists\Components\TextEntry::make('disbursement_date')->label('Décaissement')->date('d/m/Y'),
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
            'index'  => Pages\ListLoans::route('/'),
            'create' => Pages\CreateLoan::route('/create'),
            'view'   => Pages\ViewLoan::route('/{record}'),
            'edit'   => Pages\EditLoan::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $pending = static::getModel()::where('status', 'pending')->count();
        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string { return 'warning'; }
}
