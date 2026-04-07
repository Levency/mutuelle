<?php

namespace App\Filament\Resources\ContributionResource\Pages;

use App\Filament\Resources\ContributionResource;
use App\Models\Contribution;
use App\Models\Fund;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateContribution extends CreateRecord
{
    protected static string $resource = ContributionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['receipt_number'] = 'RCPT-' . strtoupper(Str::random(8));
        return $data;
    }

    protected function afterCreate(): void
    {
        // Automatically log fund inflow
        Fund::create([
            'type' => 'inflow',
            'amount' => $this->record->amount,
            'description' => "Cotisation de {$this->record->member->user->name} — {$this->record->receipt_number}",
            'reference_type' => Contribution::class,
            'reference_id' => $this->record->id,
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
