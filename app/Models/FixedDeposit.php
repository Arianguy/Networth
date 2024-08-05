<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->Int_amt = (float) $model->maturity_amt - (float) $model->principal_amt;

            $term = (int) $model->term;
            $Int_amt = (float) $model->Int_amt;

            if ($term < 365) {
                $model->Int_year = $Int_amt;
            } else {
                $model->Int_year = ($Int_amt / $term) * 365;
            }
        });
    }
}
