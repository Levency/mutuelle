<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SolidarityFundResource\Pages;
use App\Models\SolidarityFund;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SolidarityFundResource extends Resource
{
    protected static ?string $model = SolidarityFund::class;
    protected static ?string $navigationIcon = 'heroicon-o-heart';
    protected static ?string $navigationGroup = 'Finances';
    protected static ?string $navigationLabel = 'Fonds de Solidarité';
    protected static ?string $modelLabel = 'Mouvement de Solidarité';
    protected static ?string $pluralModelLabel = 'Fonds de Solidarité';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Mouvement de Fonds')
                ->schema([
                    Forms\Components\Select::make('type')
                        ->label('Type')
                        ->options(['inflow' => 'Entrée', 'outflow' => 'Sortie'])
                        ->required(),
                    Forms\Components\TextInput::make('amount')
                        ->label('Montant ($)')
                        ->numeric()
                        ->required()
                        ->minValue(1),
                    Forms\Components\TextInput::make('description')
                        ->label('Description / Motif')
                        ->required()
                        ->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn($state) => $state === 'inflow' ? 'success' : 'danger')
                    ->formatStateUsing(fn($state) => $state === 'inflow' ? 'Entrée' : 'Sortie'),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Montant')
                    ->money(\App\Models\Setting::get('currency', 'USD'))
                    ->sortable()
                    ->color(fn($record) => $record->type === 'inflow' ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('balance_after')
                    ->label('Solde après')
                    ->money(\App\Models\Setting::get('currency', 'USD')),
                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(['inflow' => 'Entrée', 'outflow' => 'Sortie']),
            ])
            ->actions([])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSolidarityFunds::route('/'),
        ];
    }
}
