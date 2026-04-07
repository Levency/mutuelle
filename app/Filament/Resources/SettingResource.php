<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Administration';
    protected static ?string $navigationLabel = 'Paramètres';
    protected static ?string $modelLabel = 'Paramètre';
    protected static ?string $pluralModelLabel = 'Paramètres';
    protected static ?int $navigationSort = 99;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('group')
                ->label('Groupe')
                ->options([
                    'general' => 'Général',
                    'loans' => 'Prêts',
                    'contributions' => 'Cotisations',
                    'help' => 'Aides',
                ])
                ->required(),
            Forms\Components\TextInput::make('key')->label('Clé (technique)')->required()->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('label')->label("Libellé (affiché)")->required(),
            Forms\Components\Select::make('type')
                ->label('Type de valeur')
                ->options(['string' => 'Texte', 'number' => 'Nombre', 'boolean' => 'Oui/Non'])
                ->default('string'),
            Forms\Components\TextInput::make('value')->label('Valeur')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\BadgeColumn::make('group')
                    ->label('Groupe')
                    ->formatStateUsing(fn($state) => match($state) {
                        'general' => 'Général', 'loans' => 'Prêts',
                        'contributions' => 'Cotisations', 'help' => 'Aides',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('label')->label('Paramètre')->searchable(),
                Tables\Columns\TextColumn::make('value')->label('Valeur'),
                Tables\Columns\TextColumn::make('type')->label('Type'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('group')
                    ->options(['general' => 'Général', 'loans' => 'Prêts', 'contributions' => 'Cotisations']),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->defaultSort('group');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}
