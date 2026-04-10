<?php

namespace App\Filament\Resources\LoanResource\Pages;

use App\Filament\Resources\LoanResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewLoan extends ViewRecord
{
    protected static string $resource = LoanResource::class;
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('recalculate')
                ->label('Recalculer l\'échéancier')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->action(function ($record) {
                    $record->recalculateSchedules();
                    Notification::make()
                        ->title('Échéancier synchronisé avec succès')
                        ->success()
                        ->send();
                }),
            Actions\EditAction::make(),
        ];
    }
}
