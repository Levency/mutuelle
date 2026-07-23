<?php

namespace App\Filament\Resources\LoanResource\RelationManagers;

use App\Models\Fund;
use App\Models\LoanRepayment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class RepaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'repayments';
    protected static ?string $title = 'Remboursements';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\DatePicker::make('payment_date')->label('Date de paiement')->required()->default(now()),
            Forms\Components\TextInput::make('amount_paid')->label('Montant payé ($)')->numeric()->required(),
            Forms\Components\Hidden::make('principal_paid')->default(0),
            Forms\Components\Hidden::make('interest_paid')->default(0),
            Forms\Components\Select::make('payment_method')
                ->label('Mode de paiement')
                ->options(['cash' => 'Espèces', 'transfer' => 'Virement', 'check' => 'Chèque'])
                ->default('cash'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('receipt_number')->label('N° Reçu')->copyable(),
            Tables\Columns\TextColumn::make('amount_paid')->label('Montant payé')->money(\App\Models\Setting::get('currency', 'USD')),
            Tables\Columns\TextColumn::make('payment_date')->label('Date')->date('d/m/Y'),
            Tables\Columns\BadgeColumn::make('payment_method')->label('Mode')
                ->formatStateUsing(fn($state) => match($state) {
                    'cash' => 'Espèces', 'transfer' => 'Virement', 'check' => 'Chèque', default => $state,
                }),
        ])->headerActions([
            Tables\Actions\CreateAction::make()
                ->mutateFormDataUsing(fn(array $data) => array_merge($data, [
                    'receipt_number' => 'RPMT-' . strtoupper(Str::random(8)),
                ]))
                ->after(function ($livewire) {
                    // Recalculer l'échéancier après l'ajout d'un remboursement
                    $livewire->getOwnerRecord()->recalculateSchedules();
                }),
        ])->actions([Tables\Actions\DeleteAction::make()]);
    }
}
