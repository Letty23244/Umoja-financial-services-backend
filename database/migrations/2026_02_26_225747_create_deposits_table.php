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
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // link to user
            $table->decimal('amount', 15, 2); // deposit amount
            $table->text('description')->nullable(); // optional description
            $table->timestamps();
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('deposits');
    }
};