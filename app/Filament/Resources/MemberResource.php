<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MemberResource\Pages;
use App\Models\Member;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class MemberResource extends Resource
{
    protected static ?string $model = Member::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Gestion des Membres';
    protected static ?string $navigationLabel = 'Membres';
    protected static ?string $modelLabel = 'Membre';
    protected static ?string $pluralModelLabel = 'Membres';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informations Personnelles')
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->label('Utilisateur')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')->required(),
                            Forms\Components\TextInput::make('email')->email()->required(),
                            Forms\Components\TextInput::make('password')->password()->required(),
                        ]),
                    Forms\Components\TextInput::make('member_number')
                        ->label('Numéro de Membre')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->default(fn () => 'MBR-' . strtoupper(substr(uniqid(), -6))),
                    Forms\Components\TextInput::make('profession')
                        ->label('Profession'),
                    Forms\Components\DatePicker::make('joined_at')
                        ->label("Date d'adhésion")
                        ->required()
                        ->default(now()),
                ])->columns(2),

            Forms\Components\Section::make('Statut & Score')
                ->schema([
                    Forms\Components\Select::make('status')
                        ->label('Statut')
                        ->options([
                            'active' => 'Actif',
                            'suspended' => 'Suspendu',
                            'pending' => 'En attente',
                        ])
                        ->required()
                        ->default('active'),
                    Forms\Components\TextInput::make('confidence_score')
                        ->label('Score de Confiance')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->default(100)
                        ->suffix('/100'),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('member_number')
                    ->label('N° Membre')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('profession')
                    ->label('Profession')
                    ->toggleable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Statut')
                    ->colors([
                        'success' => 'active',
                        'danger' => 'suspended',
                        'warning' => 'pending',
                    ])
                    ->formatStateUsing(fn($state) => match($state) {
                        'active' => 'Actif',
                        'suspended' => 'Suspendu',
                        'pending' => 'En attente',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('confidence_score')
                    ->label('Score')
                    ->suffix('/100')
                    ->sortable()
                    ->color(fn($state) => $state >= 75 ? 'success' : ($state >= 50 ? 'warning' : 'danger')),
                Tables\Columns\TextColumn::make('contributions_count')
                    ->label('Cotisations')
                    ->counts('contributions'),
                Tables\Columns\TextColumn::make('joined_at')
                    ->label("Adhésion")
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'active' => 'Actif',
                        'suspended' => 'Suspendu',
                        'pending' => 'En attente',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('suspend')
                    ->label('Suspendre')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn($record) => $record->status === 'active')
                    ->action(fn($record) => $record->update(['status' => 'suspended'])),
                Tables\Actions\Action::make('activate')
                    ->label('Activer')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->status !== 'active')
                    ->action(fn($record) => $record->update(['status' => 'active'])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Profil du Membre')
                ->schema([
                    Infolists\Components\TextEntry::make('member_number')->label('N° Membre'),
                    Infolists\Components\TextEntry::make('user.name')->label('Nom'),
                    Infolists\Components\TextEntry::make('user.email')->label('Email'),
                    Infolists\Components\TextEntry::make('profession')->label('Profession'),
                    Infolists\Components\TextEntry::make('status')
                        ->label('Statut')
                        ->badge()
                        ->color(fn($state) => match($state) {
                            'active' => 'success',
                            'suspended' => 'danger',
                            default => 'warning',
                        }),
                    Infolists\Components\TextEntry::make('confidence_score')->label('Score de confiance')->suffix('/100'),
                    Infolists\Components\TextEntry::make('joined_at')->label("Date d'adhésion")->date('d/m/Y'),
                ])->columns(2),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            MemberResource\RelationManagers\ContributionsRelationManager::class,
            MemberResource\RelationManagers\LoansRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMembers::route('/'),
            'create' => Pages\CreateMember::route('/create'),
            'view' => Pages\ViewMember::route('/{record}'),
            'edit' => Pages\EditMember::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'active')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
