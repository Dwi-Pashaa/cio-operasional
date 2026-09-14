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
        Schema::create('operational_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('expense_categories')->nullOnDelete();
            $table->string('title');
            $table->string('vendor_name')->nullable();
            $table->date('transaction_date');
            $table->double('subtotal_amount')->default(0);
            $table->boolean('has_admin_fee')->default(false);
            $table->double('admin_fee')->default(0);
            $table->double('grand_total')->default(0);
            $table->enum('payment_channel', ['manual', 'xendit'])->default('manual');
            $table->string('finance_reference_id')->nullable();
            $table->json('finance_response')->nullable();
            $table->enum('status', ['success', 'failed', 'refunded'])->default('success');
            $table->text('notes')->nullable();
            $table->string('attachment_receipt')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operational_expenses');
    }
};
