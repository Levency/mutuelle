<?php

namespace App\Filament\Resources\MemberResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class HelpRequestsRelationManager extends RelationManager
{
    protected static string $relationship = 'helpRequests';
    protected static ?string $title = "Demandes d'Aide";

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reason')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Date')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('reason')->label('Motif')->limit(40),
                Tables\Columns\TextColumn::make('amount_requested')->label('Montant demandé')
                    ->numeric(decimalPlaces: 2)->suffix(' ' . \App\Models\Setting::get('currency', 'Gourdes'))->sortable(),
                Tables\Columns\BadgeColumn::make('status')->label('Statut')
                    ->colors([
                        'warning' => 'pending', 'success' => 'validated',
                        'primary' => 'paid', 'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn($state) => match ($state) {
                        'pending' => 'En attente', 'validated' => 'Validée',
                        'paid' => 'Payée', 'rejected' => 'Rejetée',
                        default => $state,
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginationPageOptions([10, 25, 50])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Voir')
                    ->icon('heroicon-s-eye')
                    ->url(fn($record) => \App\Filament\Resources\HelpRequestResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
