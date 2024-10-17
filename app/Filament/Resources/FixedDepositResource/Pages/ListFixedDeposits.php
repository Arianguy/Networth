<?php

namespace App\Filament\Resources\FixedDepositResource\Pages;

use NumberFormatter;
use Filament\Actions;
use App\Models\FixedDeposit;
use Filament\Tables\Columns\TextColumn;
use Filament\Pages\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;
use App\Filament\Resources\FixedDepositResource;

class ListFixedDeposits extends ListRecords
{
    protected static string $resource = FixedDepositResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('bank')
                ->limit(100)
                ->label('Bank Name')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: false),
        ];
    }



    public function getTabs(): array
    {
        $formatter = new NumberFormatter('en_IN', NumberFormatter::DECIMAL);

        return [
            'All' => Tab::make('All'),
            'SBI' => Tab::make()
                ->modifyQueryUsing(function ($query) {
                    $query->where('bank', 'SBI');
                })
                ->badge(FixedDeposit::query()->where('bank', 'SBI')->count() . ' - ₹' . $formatter->format(FixedDeposit::query()->where('bank', 'SBI')->sum('principal_amt')))
                ->badgeColor('info'),
            'ICICI' => Tab::make()
                ->modifyQueryUsing(function ($query) {
                    $query->where('bank', 'ICICI');
                })
                ->badge(FixedDeposit::query()->where('bank', 'ICICI')->count() . ' - ₹' . $formatter->format(FixedDeposit::query()->where('bank', 'ICICI')->sum('principal_amt')))
                ->badgeColor('info'),
            'HDFC' => Tab::make()
                ->modifyQueryUsing(function ($query) {
                    $query->where('bank', 'HDFC');
                })
                ->badge(FixedDeposit::query()->where('bank', 'HDFC')->count() . ' - ₹' . $formatter->format(FixedDeposit::query()->where('bank', 'HDFC')->sum('principal_amt')))
                ->badgeColor('info'),
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'Valid';
    }
}
