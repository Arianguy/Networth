<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FixedDeposit extends Model
{
    protected $fillable = [
        'bank',
        'accountno',
        'principal_amt',
        'maturity_amt',
        'start_date',
        'maturity_date',
        'term',
        'int_rate',
        'Int_amt',
        'Int_year',
    ];
    protected $casts = [
        'principal_amt' => 'float',
        'start_date' => 'date',
        'maturity_date' => 'date',
        'term' => 'integer',
        'int_rate' => 'float',
        'Int_amt' => 'float',
        'Int_year' => 'float',
    ];

    public function setStartDateAttribute($value)
    {
        $this->attributes['start_date'] = $this->formatDate($value);
    }

    public function setMaturityDateAttribute($value)
    {
        $this->attributes['maturity_date'] = $this->formatDate($value);
    }

    protected function formatDate($value)
    {
        if (!$value) return null;

        try {
            if (strpos($value, '/') !== false) {
                // If the date is in d/m/Y format
                return Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
            } else {
                // If the date is already in Y-m-d format
                return Carbon::parse($value)->format('Y-m-d');
            }
        } catch (\Exception $e) {
            Log::error("Error formatting date: " . $e->getMessage(), ['value' => $value]);
            return null;
        }
    }

    public function getStartDateAttribute($value)
    {
        return $value ? Carbon::parse($value) : null;
    }

    public function getMaturityDateAttribute($value)
    {
        return $value ? Carbon::parse($value) : null;
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            Log::info('Saving FixedDeposit instance: ' . json_encode($model->toArray(), JSON_PRETTY_PRINT));

            try {
                $model->Int_amt = (float) $model->maturity_amt - (float) $model->principal_amt;
                $term = (int) $model->term;
                $Int_amt = (float) $model->Int_amt;

                if ($term < 365) {
                    $model->Int_year = $Int_amt;
                } else {
                    $model->Int_year = ($Int_amt / $term) * 365;
                }

                Log::info('Calculated values:', [
                    'Int_amt' => $model->Int_amt,
                    'Int_year' => $model->Int_year,
                ]);
            } catch (\Exception $e) {
                Log::error('Error in FixedDeposit saving event: ' . $e->getMessage(), [
                    'model' => $model->toArray(),
                    'trace' => $e->getTraceAsString(),
                ]);
                throw $e;
            }
        });

        static::saved(function ($model) {
            Log::info('FixedDeposit instance saved successfully', ['id' => $model->id]);
        });
    }
}
