<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('operational_expenses')) {
            DB::statement("ALTER TABLE operational_expenses MODIFY COLUMN status ENUM('pending', 'success', 'failed', 'refunded') NOT NULL DEFAULT 'success'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('operational_expenses')) {
            DB::statement("ALTER TABLE operational_expenses MODIFY COLUMN status ENUM('success', 'failed', 'refunded') NOT NULL DEFAULT 'success'");
        }
    }
};
