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
            Forms\Components\TextInput::make('amount_due')->label('Montant dû ($)')->numeric()->required(),
            Forms\Components\TextInput::make('amount_paid')->label('Montant payé ($)')->numeric()->default(0),
            Forms\Components\Select::make('status')->options(['pending' => 'En attente', 'paid' => 'Payé', 'late' => 'En retard'])->default('pending'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('due_date')->label('Échéance')->date('d/m/Y')->sortable(),
            Tables\Columns\TextColumn::make('amount_due')->label('Montant dû')->money('USD'),
            Tables\Columns\TextColumn::make('amount_paid')->label('Payé')->money('USD'),
            Tables\Columns\TextColumn::make('status')->label('Statut')
                ->badge()
                ->color(fn($state) => match($state) {
                    'pending' => 'warning',
                    'partial' => 'info',
                    'paid' => 'success',
                    'late' => 'danger',
                    default => 'gray',
                })
                ->formatStateUsing(fn($state) => match($state) {
                    'pending' => 'En attente', 
                    'partial' => 'Payé Partiellement',
                    'paid' => 'Payé', 
                    'late' => 'En retard', 
                    default => $state,
                }),

        ])
        ->headerActions([
            Tables\Actions\Action::make('generate_missing')
                ->label('Générer l\'échéancier')
                ->icon('heroicon-o-cpu-chip')
                ->color('info')
                ->requiresConfirmation()
                ->action(function ($livewire) {
                    $loan = $livewire->getOwnerRecord();
                    $loan->generateSchedules();
                })
                ->visible(fn ($livewire) => $livewire->getOwnerRecord()->schedules()->count() === 0),
            Tables\Actions\CreateAction::make()
        ]);
    }

}
