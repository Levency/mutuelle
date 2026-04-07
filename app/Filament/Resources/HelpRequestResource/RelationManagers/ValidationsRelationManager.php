<?php

namespace App\Filament\Resources\HelpRequestResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ValidationsRelationManager extends RelationManager
{
    protected static string $relationship = 'validations';
    protected static ?string $title = 'Validations du Comité';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')
                ->label('Membre du Comité')
                ->relationship('user', 'name')
                ->required(),
            Forms\Components\Select::make('status')
                ->label('Décision')
                ->options([
                    'validated' => '✅ Approuvé',
                    'rejected' => '❌ Rejeté',
                    'correction_needed' => '⚠️ Correction demandée',
                ])
                ->required(),
            Forms\Components\Textarea::make('comments')
                ->label('Commentaire (obligatoire si refus)')
                ->required(fn ($get) => $get('status') === 'rejected')
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('user.name')->label('Validateur'),
            Tables\Columns\BadgeColumn::make('status')
                ->label('Décision')
                ->colors([
                    'success' => 'validated',
                    'danger' => 'rejected',
                    'warning' => 'correction_needed',
                ])
                ->formatStateUsing(fn($state) => match($state) {
                    'validated' => '✅ Approuvé',
                    'rejected' => '❌ Rejeté',
                    'correction_needed' => '⚠️ Correction',
                    default => $state,
                }),
            Tables\Columns\TextColumn::make('comments')->label('Commentaire')->limit(80),
            Tables\Columns\TextColumn::make('created_at')->label('Date')->dateTime('d/m/Y H:i'),
        ])->headerActions([Tables\Actions\CreateAction::make()->label('Ajouter une validation')]);
    }
}
