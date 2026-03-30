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
        Schema::create('savings_goals', function (Blueprint $table) {
            $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('savings_wallet_id')->constrained()->cascadeOnDelete();
    $table->string('name');                          // e.g. School Fees, New Business
    $table->decimal('target_amount', 15, 2);
    $table->decimal('current_amount', 15, 2)->default(0);
    $table->date('target_date')->nullable();
    $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
    $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('savings_goals');
    }
};
