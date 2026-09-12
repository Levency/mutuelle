<?php

namespace App\Filament\Resources\MemberResource\RelationManagers;

use App\Models\Contribution;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ContributionsRelationManager extends RelationManager
{
    protected static string $relationship = 'contributions';
    protected static ?string $title = 'Historique des Cotisations';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\DatePicker::make('payment_date')->label('Date de paiement')->required()->default(now()),
            Forms\Components\TextInput::make('amount')->label('Montant (' . \App\Models\Setting::get('currency', 'HTG') . ')')->numeric()->required(),
            Forms\Components\Select::make('status')
                ->options(['paid' => 'Payé', 'pending' => 'En attente', 'late' => 'En retard'])
                ->default('paid')->required(),
            Forms\Components\TextInput::make('notes')->label('Notes'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('receipt_number')->label('N° Reçu')->copyable(),
            Tables\Columns\TextColumn::make('amount')->label('Montant')->numeric(decimalPlaces: 2)->suffix(' ' . \App\Models\Setting::get('currency', 'USD')),
            Tables\Columns\TextColumn::make('payment_date')->label('Date')->date('d/m/Y'),
            Tables\Columns\BadgeColumn::make('status')->label('Statut')
                ->colors(['success' => 'paid', 'danger' => 'late', 'warning' => 'pending'])
                ->formatStateUsing(fn($state) => match($state) {
                    'paid' => 'Payé', 'late' => 'En retard', 'pending' => 'En attente', default => $state,
                }),
        ])->headerActions([Tables\Actions\CreateAction::make()])
          ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }
}
