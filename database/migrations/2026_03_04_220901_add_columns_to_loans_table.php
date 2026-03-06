<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->decimal('amount', 12, 2)->after('user_id');
            $table->integer('duration_months')->after('amount');
            $table->decimal('remaining_balance', 12, 2)->after('duration_months');
            $table->string('status')->default('pending')->after('remaining_balance');
        });
    }

    public function down()
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn(['amount', 'duration_months', 'remaining_balance', 'status']);
        });
    }
};