<?php

namespace App\Filament\Resources\HelpRequestResource\Pages;

use App\Filament\Resources\HelpRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewHelpRequest extends ViewRecord
{
    protected static string $resource = HelpRequestResource::class;
    protected function getHeaderActions(): array { return [Actions\EditAction::make()]; }
}
