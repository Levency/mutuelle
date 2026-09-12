<?php

namespace App\Filament\Resources\MemberResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Liste en lecture seule des mouvements de solidarité générés par les
 * cotisations de ce membre. Adossée à Member::solidarityMovements(), qui
 * retourne un simple Builder (pas une relation Eloquent classique) —
 * Filament l'exploite normalement pour l'affichage et la pagination.
 */
class SolidarityMovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'solidarityMovements';
    protected static ?string $title = 'Fonds de Solidarité';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Date')->dateTime('d/m/Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('type')->label('Type')->badge()
                    ->color(fn($state) => $state === 'inflow' ? 'success' : 'danger')
                    ->formatStateUsing(fn($state) => $state === 'inflow' ? 'Entrée' : 'Sortie'),
                Tables\Columns\TextColumn::make('amount')->label('Montant')
                    ->numeric(decimalPlaces: 2)->suffix(' ' . \App\Models\Setting::get('currency', 'Gourdes'))->sortable(),
                Tables\Columns\TextColumn::make('description')->label('Description')->limit(50),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginationPageOptions([10, 25, 50]);
    }
}
