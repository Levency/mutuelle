<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FundResource\Pages;
use App\Models\Fund;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FundResource extends Resource
{
    protected static ?string $model = Fund::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-library';
    protected static ?string $navigationGroup = 'Finances';
    protected static ?string $navigationLabel = 'Journal de Caisse';
    protected static ?string $modelLabel = 'Entrée de Caisse';
    protected static ?string $pluralModelLabel = 'Journal de Caisse';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Mouvement de Fonds')
                ->schema([
                    Forms\Components\Select::make('type')
                        ->label('Type')
                        ->options(['inflow' => '↑ Entrée', 'outflow' => '↓ Sortie'])
                        ->required(),
                    Forms\Components\TextInput::make('amount')
                        ->label('Montant (' . \App\Models\Setting::get('currency', 'Gourdes') . ')')
                        ->numeric()
                        ->required()
                        ->minValue(0.01),
                    Forms\Components\Textarea::make('description')
                        ->label('Description')
                        ->required()
                        ->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Type')
                    ->colors(['success' => 'inflow', 'danger' => 'outflow'])
                    ->formatStateUsing(fn($state) => $state === 'inflow' ? '↑ Entrée' : '↓ Sortie'),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Montant')
                    ->numeric(decimalPlaces: 2)->suffix(' ' . \App\Models\Setting::get('currency', 'Gourdes'))
                    ->sortable()
                    ->color(fn($record) => $record->type === 'inflow' ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(60),
                Tables\Columns\TextColumn::make('balance_after')
                    ->label('Solde après')
                    ->numeric(decimalPlaces: 2)->suffix(' ' . \App\Models\Setting::get('currency', 'Gourdes'))
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options(['inflow' => 'Entrée', 'outflow' => 'Sortie']),
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Du'),
                        Forms\Components\DatePicker::make('until')->label('Au'),
                    ])
                    ->query(fn($query, $data) => $query
                        ->when($data['from'], fn($q, $d) => $q->whereDate('created_at', '>=', $d))
                        ->when($data['until'], fn($q, $d) => $q->whereDate('created_at', '<=', $d))
                    ),
            ])
            ->actions([Tables\Actions\ViewAction::make()])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFunds::route('/'),
            'create' => Pages\CreateFund::route('/create'),
        ];
    }
}
