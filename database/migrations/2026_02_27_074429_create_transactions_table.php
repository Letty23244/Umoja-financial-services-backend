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
    Schema::create('transactions', function (Blueprint $table) {
        $table->id();

        $table->foreignId('user_id')
              ->constrained()
              ->cascadeOnDelete();

        $table->foreignId('loan_id')
              ->nullable()
              ->constrained()
              ->nullOnDelete();

        $table->decimal('amount',10,2);

        $table->enum('type',[
            'deposit',
            'withdrawal',
            'loan_disbursement',
            'loan_repayment'
        ]);

        $table->string('reference')->nullable();

        $table->timestamps();
    });
}
};
