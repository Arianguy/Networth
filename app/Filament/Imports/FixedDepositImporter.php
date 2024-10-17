<?php

namespace App\Filament\Imports;

use App\Models\FixedDeposit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;

class FixedDepositImporter extends Importer
{
    protected static ?string $model = FixedDeposit::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('bank')
                ->requiredMapping()
                ->rules(['required', 'string']),
            ImportColumn::make('accountno')
                ->requiredMapping()
                ->rules(['required', 'string']),
            ImportColumn::make('principal_amt')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'numeric', 'min:0']),
            ImportColumn::make('start_date')
                ->requiredMapping()
                ->rules(['required'])
                ->castStateUsing(fn($state) => static::parseDate($state)),
            ImportColumn::make('maturity_date')
                ->requiredMapping()
                ->rules(['required'])
                ->castStateUsing(fn($state) => static::parseDate($state)),
            // ImportColumn::make('maturity_date')
            //     ->requiredMapping()
            //     ->rules(['required', 'date', 'date_format:d/m/Y']),
            ImportColumn::make('term')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer', 'min:1']),
            ImportColumn::make('int_rate')
                ->requiredMapping()
                ->numeric(decimalPlaces: 2)
                ->rules(['required', 'numeric', 'min:0']),
        ];
    }

    protected static function parseDate(?string $date): ?string
    {
        if (!$date) return null;
        try {
            return \Carbon\Carbon::createFromFormat('d/m/Y', $date)->format('Y-m-d');
        } catch (\Exception $e) {
            Log::error('Date parsing error: ' . $e->getMessage(), ['date' => $date]);
            return null;
        }
    }
    public function resolveRecord(): ?FixedDeposit
    {
        Log::info('Importing data: ' . json_encode($this->data, JSON_PRETTY_PRINT));

        DB::beginTransaction();

        try {
            $fixedDeposit = new FixedDeposit();
            Log::info('Creating new FixedDeposit instance');

            // The dates should already be parsed by the castStateUsing method
            $fixedDeposit->fill($this->data);
            Log::info('Filled FixedDeposit instance with data: ' . json_encode($fixedDeposit->toArray(), JSON_PRETTY_PRINT));

            if ($fixedDeposit->save()) {
                DB::commit();
                Log::info('Saved FixedDeposit instance successfully', ['id' => $fixedDeposit->id]);

                // Verify the save
                $savedDeposit = FixedDeposit::find($fixedDeposit->id);
                if ($savedDeposit) {
                    Log::info('FixedDeposit verified in database', [
                        'id' => $savedDeposit->id,
                        'data' => json_encode($savedDeposit->toArray(), JSON_PRETTY_PRINT)
                    ]);
                } else {
                    Log::error('FixedDeposit not found in database after save', ['id' => $fixedDeposit->id]);
                }

                return $fixedDeposit;
            } else {
                DB::rollBack();
                Log::error('Failed to save FixedDeposit instance');
                return null;
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Exception occurred while saving FixedDeposit: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'data' => $this->data
            ]);
            return null;
        }
    }
    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your fixed deposit import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';
        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }
        return $body;
    }
}
