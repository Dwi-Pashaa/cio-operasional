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
        Schema::create('operational_expense_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_id')->constrained('operational_expenses')->cascadeOnDelete();
            $table->foreignId('item_id')->nullable()->constrained('items')->nullOnDelete();
            $table->string('item_name');
            $table->double('quantity')->default(1);
            $table->string('unit')->default('pcs');
            $table->double('unit_price')->default(0);
            $table->double('subtotal')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operational_expense_items');
    }
};
