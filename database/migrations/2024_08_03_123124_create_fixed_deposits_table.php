<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fixed_deposits', function (Blueprint $table) {
            $table->id();
            $table->string('bank');
            $table->string('accountno');
            $table->decimal('principal_amt');
            $table->decimal('maturity_amt');
            $table->date('start_date');
            $table->date('maturity_date');
            $table->string('term');
            $table->decimal('int_rate');
            $table->decimal('Int_amt');
            $table->decimal('Int_year');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fixed_deposits');
    }
};
