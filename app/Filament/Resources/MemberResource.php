<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MemberResource\Pages;
use App\Filament\Resources\MemberResource\RelationManagers;
use App\Models\Member;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
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

            // ── Section 1 : Identification ──────────────────────────────────
            Forms\Components\Section::make('Identification du Membre')
                ->schema([
                    Forms\Components\TextInput::make('member_number')
                        ->label('N° Membre')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->default(fn() => 'M-' . str_pad(Member::count() + 1, 4, '0', STR_PAD_LEFT))
                        ->readOnly(),

                    Forms\Components\Select::make('status')
                        ->label('Statut')
                        ->options([
                            'active'    => 'Actif',
                            'suspended' => 'Suspendu',
                            'inactive'  => 'Inactif',
                        ])
                        ->default('active')
                        ->required(),

                    Forms\Components\DatePicker::make('joined_at')
                        ->label('Date d\'adhésion')
                        ->default(now())
                        ->required(),

                    Forms\Components\TextInput::make('confidence_score')
                        ->label('Score de confiance')
                        ->numeric()
                        ->default(100)
                        ->minValue(0)
                        ->maxValue(100)
                        ->suffix('%'),
                ])->columns(2),

            // ── Section 2 : Compte utilisateur (optionnel) ──────────────────
            Forms\Components\Section::make('Compte Utilisateur')
                ->description('Optionnel — le membre peut ne pas avoir de compte de connexion')
                ->collapsed()
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->label('Lier à un compte utilisateur')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->placeholder('Aucun compte (saisir les infos ci-dessous)'),
                ]),

            // ── Section 3 : Informations personnelles directes ───────────────
            Forms\Components\Section::make('Informations Personnelles')
                ->description('À remplir si le membre n\'a pas de compte utilisateur')
                ->schema([
                    Forms\Components\TextInput::make('first_name')
                        ->label('Prénom')
                        ->required(fn(Forms\Get $get) => !$get('user_id')),
                    Forms\Components\TextInput::make('last_name')
                        ->label('Nom de famille')
                        ->required(fn(Forms\Get $get) => !$get('user_id')),
                    Forms\Components\TextInput::make('phone')
                        ->label('Téléphone')
                        ->tel(),
                    Forms\Components\TextInput::make('national_id')
                        ->label('N° d\'identité nationale (CIN)'),
                    Forms\Components\DatePicker::make('birth_date')
                        ->label('Date de naissance'),
                    Forms\Components\TextInput::make('profession')
                        ->label('Profession'),
                    Forms\Components\Textarea::make('address')
                        ->label('Adresse complète')
                        ->rows(2)
                        ->columnSpanFull(),
                ])->columns(2),

            // ── Section 4 : Contact d'urgence ────────────────────────────────
            Forms\Components\Section::make('Contact d\'Urgence')
                ->collapsed()
                ->schema([
                    Forms\Components\TextInput::make('emergency_contact')
                        ->label('Nom du contact d\'urgence'),
                    Forms\Components\TextInput::make('emergency_phone')
                        ->label('Téléphone d\'urgence')
                        ->tel(),
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
                    ->copyable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('full_name')
                    ->label('Nom complet')
                    ->searchable(query: function (\Illuminate\Database\Eloquent\Builder $query, string $search): \Illuminate\Database\Eloquent\Builder {
                        return $query
                            ->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhereHas('user', function ($q) use ($search) {
                                $q->where('name', 'like', "%{$search}%");
                            });
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Téléphone')
                    ->searchable(),

                Tables\Columns\TextColumn::make('total_contributed')
                    ->label('Total cotisé')
                    ->numeric(decimalPlaces: 2)->suffix(' ' . \App\Models\Setting::get('currency', 'Gourdes'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('confidence_score')
                    ->label('Score')
                    ->suffix('%')
                    ->sortable()
                    ->color(fn($state) => match(true) {
                        $state >= 80 => 'success',
                        $state >= 60 => 'warning',
                        default      => 'danger',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'active'    => 'success',
                        'suspended' => 'danger',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn($state) => match($state) {
                        'active'    => 'Actif',
                        'suspended' => 'Suspendu',
                        'inactive'  => 'Inactif',
                        default     => $state,
                    }),

                Tables\Columns\TextColumn::make('joined_at')
                    ->label('Adhésion')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\IconColumn::make('has_access_code')
                    ->label('Portail')
                    ->getStateUsing(fn(Member $record) => $record->hasAccessCode())
                    ->boolean()
                    ->trueIcon('heroicon-o-key')
                    ->falseIcon('heroicon-o-lock-closed')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip(fn(Member $record) => $record->hasAccessCode()
                        ? "Code d'accès actif — dernière génération le " . $record->access_code_generated_at?->format('d/m/Y')
                        : "Aucun code d'accès au portail membre"),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options(['active' => 'Actif', 'suspended' => 'Suspendu', 'inactive' => 'Inactif']),
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
                    ->action(function ($record) {
                        $record->update(['status' => 'suspended']);
                        Notification::make()->title('Membre suspendu')->danger()->send();
                    }),
                Tables\Actions\Action::make('reactivate')
                    ->label('Réactiver')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn($record) => $record->status === 'suspended')
                    ->action(function ($record) {
                        $record->update(['status' => 'active', 'confidence_score' => 100]);
                        Notification::make()->title('Membre réactivé')->success()->send();
                    }),
                Tables\Actions\Action::make('generateAccessCode')
                    ->label(fn(Member $record) => $record->hasAccessCode() ? 'Régénérer le code' : "Créer un code d'accès")
                    ->icon('heroicon-o-key')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading("Générer un code d'accès au portail")
                    ->modalDescription('Un nouveau code à 6 chiffres sera créé et remplacera immédiatement l\'ancien, le cas échéant. Il ne sera affiché qu\'une seule fois.')
                    ->modalSubmitActionLabel('Générer')
                    ->action(function (Member $record) {
                        $code = $record->generateAccessCode();

                        Notification::make()
                            ->title("Code d'accès généré pour {$record->full_name}")
                            ->body("Code : {$code}\n\nNotez-le et communiquez-le au membre immédiatement — il ne sera plus jamais affiché.")
                            ->success()
                            ->persistent()
                            ->send();
                    }),
                Tables\Actions\Action::make('revokeAccessCode')
                    ->label('Révoquer le code')
                    ->icon('heroicon-o-lock-closed')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription('Le membre ne pourra plus se connecter au portail tant qu\'un nouveau code ne lui sera pas généré.')
                    ->visible(fn(Member $record) => $record->hasAccessCode())
                    ->action(function (Member $record) {
                        $record->revokeAccessCode();
                        Notification::make()->title("Code d'accès révoqué")->warning()->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('joined_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informations Personnelles')
                    ->description('Les détails personnels du membre')
                    ->icon('heroicon-o-identification')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('member_number')->label('N° Membre')->weight('bold')->badge()->color('primary'),
                                Infolists\Components\TextEntry::make('first_name')->label('Prénom')->icon('heroicon-m-user'),
                                Infolists\Components\TextEntry::make('last_name')->label('Nom de famille')->icon('heroicon-m-user'),
                                Infolists\Components\TextEntry::make('phone')->label('Téléphone')->icon('heroicon-m-phone'),
                                Infolists\Components\TextEntry::make('national_id')->label('N° Identité')->icon('heroicon-m-identification'),
                                Infolists\Components\TextEntry::make('birth_date')->label('Date de naissance')->date('d/m/Y')->icon('heroicon-m-calendar'),
                                Infolists\Components\TextEntry::make('profession')->label('Profession')->icon('heroicon-m-briefcase'),
                                Infolists\Components\TextEntry::make('address')->label('Adresse complète')->columnSpanFull()->icon('heroicon-m-map-pin'),
                            ]),
                    ])->collapsible(),
                Infolists\Components\Section::make('Statut et Adhésion')
                    ->description('Informations sur le compte de la mutuelle')
                    ->icon('heroicon-o-chart-bar')
                    ->schema([
                        Infolists\Components\Grid::make(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('status')->label('Statut')->badge()
                                    ->color(fn($state) => match($state) {
                                        'active'    => 'success',
                                        'suspended' => 'danger',
                                        default     => 'gray',
                                    })
                                    ->formatStateUsing(fn($state) => match($state) {
                                        'active'    => 'Actif',
                                        'suspended' => 'Suspendu',
                                        'inactive'  => 'Inactif',
                                        default     => $state,
                                    })->icon('heroicon-m-check-badge'),
                                Infolists\Components\TextEntry::make('joined_at')->label('Date d\'adhésion')->date('d/m/Y')->icon('heroicon-m-calendar'),
                                Infolists\Components\TextEntry::make('confidence_score')->label('Score de confiance')->suffix('%')->badge()->color('warning'),
                                Infolists\Components\TextEntry::make('total_contributed')->label('Total cotisé')
                                    ->numeric(decimalPlaces: 2)->suffix(' ' . \App\Models\Setting::get('currency', 'Gourdes'))
                                    ->badge()->color('success'),
                            ]),
                    ])->collapsible(),
                Infolists\Components\Section::make('Contact d\'Urgence')
                    ->description('En cas de nécessité')
                    ->icon('heroicon-o-phone')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('emergency_contact')->label('Nom du contact')->icon('heroicon-m-user-group'),
                                Infolists\Components\TextEntry::make('emergency_phone')->label('Téléphone')->icon('heroicon-m-phone'),
                            ]),
                    ])->collapsible(),
                Infolists\Components\Section::make('Portail Membre')
                    ->description('Espace de consultation détaché de l\'administration — utilisez les actions "Créer/Régénérer le code" dans la liste des membres')
                    ->icon('heroicon-o-key')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('has_access_code')
                                    ->label('Code d\'accès')
                                    ->state(fn(Member $record) => $record->hasAccessCode() ? 'Actif' : 'Aucun')
                                    ->badge()
                                    ->color(fn(Member $record) => $record->hasAccessCode() ? 'success' : 'gray'),
                                Infolists\Components\TextEntry::make('access_code_generated_at')->label('Dernière génération')->dateTime('d/m/Y H:i')->placeholder('—'),
                                Infolists\Components\TextEntry::make('portal_last_login_at')->label('Dernière connexion au portail')->dateTime('d/m/Y H:i')->placeholder('Jamais connecté'),
                                Infolists\Components\TextEntry::make('portal_last_login_ip')->label('Dernière IP')->placeholder('—'),
                            ]),
                    ])->collapsible(),
            ]);
    }

    public static function getRelationManagers(): array
    {
        return [
            RelationManagers\ContributionsRelationManager::class,
            RelationManagers\LoansRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListMembers::route('/'),
            'create' => Pages\CreateMember::route('/create'),
            'view'   => Pages\ViewMember::route('/{record}'),
            'edit'   => Pages\EditMember::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $suspended = Member::where('status', 'suspended')->count();
        return $suspended > 0 ? (string) $suspended : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
}
