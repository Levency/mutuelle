<?php

namespace App\Filament\Resources\HelpRequestResource\Pages;

use App\Filament\Resources\HelpRequestResource;
use Filament\Resources\Pages\CreateRecord;

class CreateHelpRequest extends CreateRecord
{
    protected static string $resource = HelpRequestResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
