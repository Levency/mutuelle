<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContributionResource\Pages;
use App\Models\Contribution;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ContributionResource extends Resource
{
    protected static ?string $model = Contribution::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Finances';
    protected static ?string $navigationLabel = 'Cotisations';
    protected static ?string $modelLabel = 'Cotisation';
    protected static ?string $pluralModelLabel = 'Cotisations';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Enregistrement de Paiement')
                ->schema([
                    Forms\Components\Select::make('member_id')
                        ->label('Membre')
                        ->relationship('member', 'member_number')
                        ->getOptionLabelFromRecordUsing(fn($record) => "{$record->member_number} — {$record->full_name}")
                        ->searchable()
                        ->preload()
                        ->required(),
                    Forms\Components\DatePicker::make('payment_date')
                        ->label('Date de paiement')
                        ->required()
                        ->default(now()),
                    Forms\Components\TextInput::make('amount')
                        ->label('Montant total ($)')
                        ->numeric()
                        ->required()
                        ->minValue(1)
                        ->live(debounce: 500),
                    Forms\Components\Select::make('status')
                        ->label('Statut')
                        ->options([
                            'paid'    => 'Payé',
                            'pending' => 'En attente',
                            'late'    => 'En retard',
                        ])
                        ->default('paid')
                        ->required(),

                    Forms\Components\Placeholder::make('split_info')
                        ->label('💡 Répartition prévue')
                        ->visible(fn(Forms\Get $get) => $get('amount') > 0)
                        ->content(function (Forms\Get $get): string {
                            $total = floatval($get('amount') ?? 0);
                            $rate = floatval(Setting::get('solidarity_rate', 20)) / 100;
                            $sol = round($total * $rate, 2);
                            $main = $total - $sol;
                            $currency = \App\Models\Setting::get('currency', 'Gourdes');
                            return "Caisse : " . number_format($main, 2) . " {$currency} | Solidarité : " . number_format($sol, 2) . " {$currency} (" . ($rate * 100) . "%)";
                        }),

                    Forms\Components\Textarea::make('notes')
                        ->label('Notes')
                        ->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('receipt_number')
                    ->label('N° Reçu')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('member.full_name')
                    ->label('Membre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('member.member_number')
                    ->label('N° Membre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Montant')
                    ->numeric(decimalPlaces: 2)->suffix(' ' . \App\Models\Setting::get('currency', 'Gourdes'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'paid'    => 'success',
                        'pending' => 'warning',
                        'late'    => 'danger',
                        default   => 'gray',
                    })
                    ->formatStateUsing(fn($state) => match($state) {
                        'paid'    => 'Payé',
                        'pending' => 'En attente',
                        'late'    => 'En retard',
                        default   => $state,
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'paid'    => 'Payé',
                        'pending' => 'En attente',
                        'late'    => 'En retard',
                    ]),
                Tables\Filters\Filter::make('payment_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Du'),
                        Forms\Components\DatePicker::make('until')->label('Au'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn($q, $d) => $q->whereDate('payment_date', '>=', $d))
                            ->when($data['until'], fn($q, $d) => $q->whereDate('payment_date', '<=', $d));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('payment_date', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Détails de la Cotisation')
                ->schema([
                    Infolists\Components\Grid::make(3)
                        ->schema([
                            Infolists\Components\TextEntry::make('receipt_number')
                                ->label('N° de Reçu')
                                ->weight('bold')
                                ->copyable()
                                ->color('primary'),
                            Infolists\Components\TextEntry::make('payment_date')
                                ->label('Date de Paiement')
                                ->date('d F Y'),
                            Infolists\Components\TextEntry::make('status')
                                ->label('Statut')
                                ->badge()
                                ->color(fn($state) => match($state) {
                                    'paid'    => 'success',
                                    'pending' => 'warning',
                                    'late'    => 'danger',
                                    default   => 'gray',
                                })
                                ->formatStateUsing(fn($state) => match($state) {
                                    'paid'    => 'Payé',
                                    'pending' => 'En attente',
                                    'late'    => 'En retard',
                                    default   => $state,
                                }),
                        ]),
                ]),

            Infolists\Components\Section::make('Informations Membre & Financières')
                ->schema([
                    Infolists\Components\Grid::make(2)
                        ->schema([
                            Infolists\Components\Group::make([
                                Infolists\Components\TextEntry::make('member.full_name')
                                    ->label('Membre'),
                                Infolists\Components\TextEntry::make('member.member_number')
                                    ->label('Identifiant Membre'),
                            ]),
                            Infolists\Components\Group::make([
                                Infolists\Components\TextEntry::make('amount')
                                    ->label('Montant Total')
                                    ->numeric(decimalPlaces: 2)->suffix(' ' . \App\Models\Setting::get('currency', 'Gourdes'))
                                    ->size('lg')
                                    ->weight('bold'),
                                Infolists\Components\TextEntry::make('split_preview')
                                    ->label('Répartition (Caisse / Solidarité)')
                                    ->getStateUsing(function ($record) {
                                        $total = (float) $record->amount;
                                        $rate = (float) Setting::get('solidarity_rate', 20) / 100;
                                        $sol = $total * $rate;
                                        $main = $total - $sol;
                                        $currency = \App\Models\Setting::get('currency', 'Gourdes');
                                        return number_format($main, 2) . " {$currency} / " . number_format($sol, 2) . " {$currency}";
                                    })
                                    ->icon('heroicon-o-arrows-right-left'),
                            ]),
                        ]),
                    Infolists\Components\TextEntry::make('notes')
                        ->label('Notes complémentaires')
                        ->placeholder('Aucune note saisie.')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListContributions::route('/'),
            'create' => Pages\CreateContribution::route('/create'),
            'view'   => Pages\ViewContribution::route('/{record}'),
            'edit'   => Pages\EditContribution::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $lateCount = static::getModel()::where('status', 'late')->count();
        return $lateCount > 0 ? (string) $lateCount : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
}
