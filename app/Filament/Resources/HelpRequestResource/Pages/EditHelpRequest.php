<?php

namespace App\Filament\Resources\HelpRequestResource\Pages;

use App\Filament\Resources\HelpRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHelpRequest extends EditRecord
{
    protected static string $resource = HelpRequestResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
}
