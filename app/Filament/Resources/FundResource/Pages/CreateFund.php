<?php

namespace App\Filament\Resources\FundResource\Pages;

use App\Filament\Resources\FundResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFund extends CreateRecord
{
    protected static string $resource = FundResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
