<?php

namespace App\Filament\Widgets;

use App\Models\Contribution;
use App\Models\Fund;
use App\Models\HelpRequest;
use App\Models\Loan;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentActivitiesWidget extends BaseWidget
{
    protected static ?string $heading = '🕐 Activités Récentes';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Fund::query()->latest()->limit(10)
            )
            ->columns([
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Type')
                    ->colors(['success' => 'inflow', 'danger' => 'outflow'])
                    ->formatStateUsing(fn($state) => $state === 'inflow' ? '↑ Entrée' : '↓ Sortie'),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Montant')
                    ->numeric(decimalPlaces: 2)->suffix(' ' . \App\Models\Setting::get('currency', 'USD')),
                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(70),
                Tables\Columns\TextColumn::make('balance_after')
                    ->label('Solde après')
                    ->numeric(decimalPlaces: 2)->suffix(' ' . \App\Models\Setting::get('currency', 'USD')),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ]);
    }
}
