<?php

namespace App\Filament\Resources\FixedDepositResource\Pages;

use App\Filament\Resources\FixedDepositResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFixedDeposit extends EditRecord
{
    protected static string $resource = FixedDepositResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
