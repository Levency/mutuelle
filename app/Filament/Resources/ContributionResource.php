<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContributionResource\Pages;
use App\Models\Contribution;
use Filament\Forms;
use Filament\Forms\Form;
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
                        ->getOptionLabelFromRecordUsing(fn($record) => "{$record->member_number} — {$record->user->name}")
                        ->searchable()
                        ->preload()
                        ->required(),
                    Forms\Components\DatePicker::make('payment_date')
                        ->label('Date de paiement')
                        ->required()
                        ->default(now()),
                    Forms\Components\TextInput::make('amount')
                        ->label('Montant (HTG)')
                        ->numeric()
                        ->required()
                        ->minValue(1),
                    Forms\Components\Select::make('status')
                        ->label('Statut')
                        ->options(['paid' => 'Payé', 'pending' => 'En attente', 'late' => 'En retard'])
                        ->default('paid')
                        ->required(),
                    Forms\Components\TextInput::make('notes')->label('Notes')->columnSpanFull(),
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
                Tables\Columns\TextColumn::make('member.user.name')
                    ->label('Membre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('member.member_number')
                    ->label('N° Membre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Montant')
                    ->money('HTG')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Statut')
                    ->colors([
                        'success' => 'paid',
                        'warning' => 'pending',
                        'danger' => 'late',
                    ])
                    ->formatStateUsing(fn($state) => match($state) {
                        'paid' => 'Payé',
                        'pending' => 'En attente',
                        'late' => 'En retard',
                        default => $state,
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options(['paid' => 'Payé', 'pending' => 'En attente', 'late' => 'En retard']),
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
                Tables\Actions\Action::make('receipt')
                    ->label('Reçu')
                    ->icon('heroicon-o-document-text')
                    ->url(fn($record) => route('contributions.receipt', $record))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
            ->defaultSort('payment_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContributions::route('/'),
            'create' => Pages\CreateContribution::route('/create'),
            'edit' => Pages\EditContribution::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $lateCount = static::getModel()::where('status', 'late')->count();
        return $lateCount > 0 ? (string)$lateCount : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
}
