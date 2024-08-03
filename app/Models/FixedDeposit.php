<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FixedDeposit extends Model
{
    use HasFactory;
    protected $fillable = [

        'bank', 'accountno', 'principal_amt', 'maturity_amt', 'start_date', 'maturity_date', 'term', 'int_rate', 'Int_amt', 'Int_year'
    ];
}
