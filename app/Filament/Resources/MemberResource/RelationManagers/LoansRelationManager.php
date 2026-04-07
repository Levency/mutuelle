<?php

namespace App\Filament\Resources\MemberResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class LoansRelationManager extends RelationManager
{
    protected static string $relationship = 'loans';
    protected static ?string $title = 'Prêts';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('principal_amount')->label('Montant (HTG)')->numeric()->required(),
            Forms\Components\TextInput::make('interest_rate')->label('Taux (%)')->numeric()->default(0),
            Forms\Components\TextInput::make('term_months')->label('Durée (mois)')->numeric()->default(12),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('principal_amount')->label('Capital')->money('HTG'),
            Tables\Columns\TextColumn::make('total_to_repay')->label('Total à rembourser')->money('HTG'),
            Tables\Columns\TextColumn::make('balance_remaining')->label('Solde restant')->money('HTG'),
            Tables\Columns\BadgeColumn::make('status')->label('Statut')
                ->colors([
                    'warning' => 'pending', 'success' => 'active',
                    'gray' => 'repaid', 'danger' => 'defaulted', 'info' => 'rejected',
                ]),
        ]);
    }
}
