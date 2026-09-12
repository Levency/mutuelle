<?php

namespace App\Filament\Resources\MemberResource\Pages;

use App\Filament\Resources\MemberResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewMember extends ViewRecord
{
    protected static string $resource = MemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generateAccessCode')
                ->label(fn() => $this->record->hasAccessCode() ? 'Régénérer le code d\'accès' : "Créer un code d'accès")
                ->icon('heroicon-o-key')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading("Générer un code d'accès au portail")
                ->modalDescription('Un nouveau code à 6 chiffres sera créé et remplacera l\'ancien. Il ne sera affiché qu\'une seule fois.')
                ->modalSubmitActionLabel('Générer')
                ->action(function () {
                    $code = $this->record->generateAccessCode();
                    \Filament\Notifications\Notification::make()
                        ->title("Nouveau code généré pour {$this->record->full_name}")
                        ->body("Code : {$code}\n\nNotez-le et communiquez-le au membre immédiatement — il ne sera plus jamais affiché pour des raisons de sécurité.")
                        ->success()
                        ->persistent()
                        ->send();
                }),
            Actions\EditAction::make(),
        ];
    }
}
