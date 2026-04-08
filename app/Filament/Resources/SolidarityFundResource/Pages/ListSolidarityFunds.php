<?php

namespace App\Filament\Resources\SolidarityFundResource\Pages;

use App\Filament\Resources\SolidarityFundResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSolidarityFunds extends ListRecords
{
    protected static string $resource = SolidarityFundResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
