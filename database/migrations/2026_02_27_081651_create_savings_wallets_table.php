<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Directly alter the column in PostgreSQL to add a default of 0
        DB::statement('ALTER TABLE locked_savings ALTER COLUMN saving_wallet_id SET DEFAULT 0');
        DB::statement('ALTER TABLE locked_savings ALTER COLUMN saving_wallet_id DROP NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE locked_savings ALTER COLUMN saving_wallet_id SET NOT NULL');
        DB::statement('ALTER TABLE locked_savings ALTER COLUMN saving_wallet_id DROP DEFAULT');
    }
};