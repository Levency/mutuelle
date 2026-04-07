<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HelpRequestResource\Pages;
use App\Models\HelpRequest;
use App\Models\Fund;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class HelpRequestResource extends Resource
{
    protected static ?string $model = HelpRequest::class;
    protected static ?string $navigationIcon = 'heroicon-o-inbox-stack';
    protected static ?string $navigationGroup = 'Demandes';
    protected static ?string $navigationLabel = "Demandes d'Aide";
    protected static ?string $modelLabel = "Demande d'Aide";
    protected static ?string $pluralModelLabel = "Demandes d'Aide";
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Demande')
                ->schema([
                    Forms\Components\Select::make('member_id')
                        ->label('Membre')
                        ->relationship('member', 'member_number')
                        ->getOptionLabelFromRecordUsing(fn($record) => "{$record->member_number} — {$record->user->name}")
                        ->searchable()
                        ->preload()
                        ->required(),
                    Forms\Components\TextInput::make('reason')
                        ->label('Motif de la demande')
                        ->required(),
                    Forms\Components\TextInput::make('amount_requested')
                        ->label('Montant demandé (HTG)')
                        ->numeric()
                        ->required()
                        ->minValue(1),
                    Forms\Components\Select::make('status')
                        ->label('Statut')
                        ->options([
                            'pending' => 'En attente',
                            'validated' => 'Validée',
                            'rejected' => 'Rejetée',
                            'paid' => 'Payée',
                        ])
                        ->default('pending')
                        ->required(),
                    Forms\Components\Textarea::make('description')
                        ->label('Description / Contexte')
                        ->columnSpanFull(),
                    Forms\Components\FileUpload::make('document_path')
                        ->label('Justificatif(s)')
                        ->disk('public')
                        ->directory('help-documents')
                        ->acceptedFileTypes(['application/pdf', 'image/*'])
                        ->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('member.user.name')->label('Membre')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('reason')->label('Motif')->limit(40),
                Tables\Columns\TextColumn::make('amount_requested')->label('Montant demandé')->money('HTG'),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Statut')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'validated',
                        'danger' => 'rejected',
                        'info' => 'paid',
                    ])
                    ->formatStateUsing(fn($state) => match($state) {
                        'pending' => 'En attente',
                        'validated' => 'Validée',
                        'rejected' => 'Rejetée',
                        'paid' => 'Payée',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('created_at')->label('Soumise le')->date('d/m/Y')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'En attente',
                        'validated' => 'Validée',
                        'rejected' => 'Rejetée',
                        'paid' => 'Payée',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Valider')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn($record) => $record->status === 'pending')
                    ->action(fn($record) => $record->update(['status' => 'validated'])),
                Tables\Actions\Action::make('pay')
                    ->label('Marquer Payée')
                    ->icon('heroicon-o-banknotes')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn($record) => $record->status === 'validated')
                    ->action(function ($record) {
                        $record->update(['status' => 'paid']);
                        Fund::create([
                            'type' => 'outflow',
                            'amount' => $record->amount_requested,
                            'description' => "Aide accordée à {$record->member->user->name} — {$record->reason}",
                            'reference_type' => HelpRequest::class,
                            'reference_id' => $record->id,
                        ]);
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Rejeter')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn($record) => $record->status === 'pending')
                    ->action(fn($record) => $record->update(['status' => 'rejected'])),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make("Demande d'Aide")
                ->schema([
                    Infolists\Components\TextEntry::make('member.user.name')->label('Membre'),
                    Infolists\Components\TextEntry::make('reason')->label('Motif'),
                    Infolists\Components\TextEntry::make('amount_requested')->label('Montant')->money('HTG'),
                    Infolists\Components\TextEntry::make('status')->label('Statut')->badge(),
                    Infolists\Components\TextEntry::make('description')->label('Description'),
                ])->columns(2),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            HelpRequestResource\RelationManagers\ValidationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHelpRequests::route('/'),
            'create' => Pages\CreateHelpRequest::route('/create'),
            'view' => Pages\ViewHelpRequest::route('/{record}'),
            'edit' => Pages\EditHelpRequest::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $pending = static::getModel()::where('status', 'pending')->count();
        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string { return 'warning'; }
}
