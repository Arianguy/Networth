<?php

namespace App\Filament\Resources\FixedDepositResource\Pages;

use App\Filament\Resources\FixedDepositResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFixedDeposits extends ListRecords
{
    protected static string $resource = FixedDepositResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
