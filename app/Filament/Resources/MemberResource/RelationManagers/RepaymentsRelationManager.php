<?php

namespace App\Filament\Resources\MemberResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class RepaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'loanRepayments';
    protected static ?string $title = 'Remboursements';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('receipt_number')
            ->columns([
                Tables\Columns\TextColumn::make('payment_date')->label('Date')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('loan_id')
                    ->label('Prêt')
                    ->formatStateUsing(fn($state) => "#{$state}"),
                Tables\Columns\TextColumn::make('amount_paid')->label('Montant payé')
                    ->numeric(decimalPlaces: 2)->suffix(' ' . \App\Models\Setting::get('currency', 'Gourdes'))->sortable(),
                Tables\Columns\TextColumn::make('principal_paid')->label('Dont capital')
                    ->numeric(decimalPlaces: 2)->suffix(' ' . \App\Models\Setting::get('currency', 'Gourdes')),
                Tables\Columns\TextColumn::make('interest_paid')->label('Dont intérêt')
                    ->numeric(decimalPlaces: 2)->suffix(' ' . \App\Models\Setting::get('currency', 'Gourdes')),
                Tables\Columns\TextColumn::make('payment_method')->label('Moyen')->placeholder('—'),
                Tables\Columns\TextColumn::make('receipt_number')->label('N° Reçu')->copyable()->placeholder('—'),
            ])
            ->defaultSort('payment_date', 'desc')
            ->paginationPageOptions([10, 25, 50])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Voir le prêt')
                    ->icon('heroicon-s-eye')
                    ->url(fn($record) => \App\Filament\Resources\LoanResource::getUrl('view', ['record' => $record->loan_id])),
            ]);
    }
}
