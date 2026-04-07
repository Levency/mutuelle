<?php

namespace App\Filament\Resources\LoanResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class SchedulesRelationManager extends RelationManager
{
    protected static string $relationship = 'schedules';
    protected static ?string $title = 'Échéancier de Remboursement';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\DatePicker::make('due_date')->label('Date d\'échéance')->required(),
            Forms\Components\TextInput::make('amount_due')->label('Montant dû (HTG)')->numeric()->required(),
            Forms\Components\TextInput::make('amount_paid')->label('Montant payé (HTG)')->numeric()->default(0),
            Forms\Components\Select::make('status')->options(['pending' => 'En attente', 'paid' => 'Payé', 'late' => 'En retard'])->default('pending'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('due_date')->label('Échéance')->date('d/m/Y')->sortable(),
            Tables\Columns\TextColumn::make('amount_due')->label('Montant dû')->money('HTG'),
            Tables\Columns\TextColumn::make('amount_paid')->label('Payé')->money('HTG'),
            Tables\Columns\BadgeColumn::make('status')->label('Statut')
                ->colors(['warning' => 'pending', 'success' => 'paid', 'danger' => 'late'])
                ->formatStateUsing(fn($state) => match($state) {
                    'pending' => 'En attente', 'paid' => 'Payé', 'late' => 'En retard', default => $state,
                }),
        ])->headerActions([Tables\Actions\CreateAction::make()]);
    }
}
