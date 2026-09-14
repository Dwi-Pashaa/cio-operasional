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
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'notification_channel')) {
                $table->enum('notification_channel', ['whatsapp', 'email', 'both', 'none'])->default('whatsapp')->after('telp');
            }
            if (!Schema::hasColumn('settings', 'admin_fee')) {
                $table->double('admin_fee')->default(0)->after('notification_channel');
            }
            if (!Schema::hasColumn('settings', 'xendit_secret_key')) {
                $table->string('xendit_secret_key')->nullable()->after('admin_fee');
            }
            if (!Schema::hasColumn('settings', 'xendit_webhook_token')) {
                $table->string('xendit_webhook_token')->nullable()->after('xendit_secret_key');
            }
        });

        if (Schema::hasTable('transfers')) {
            Schema::table('transfers', function (Blueprint $table) {
                if (!Schema::hasColumn('transfers', 'gross_amount')) {
                    $table->double('gross_amount')->nullable()->after('amount');
                }
                if (!Schema::hasColumn('transfers', 'admin_fee')) {
                    $table->double('admin_fee')->default(0)->after('gross_amount');
                }
                if (!Schema::hasColumn('transfers', 'balance_type')) {
                    $table->enum('balance_type', ['manual', 'xendit'])->default('manual')->after('payment_method');
                }
                if (!Schema::hasColumn('transfers', 'finance_reference_id')) {
                    $table->string('finance_reference_id')->nullable()->after('balance_type');
                }
                if (!Schema::hasColumn('transfers', 'xendit_disbursement_id')) {
                    $table->string('xendit_disbursement_id')->nullable()->after('finance_reference_id');
                }
                if (!Schema::hasColumn('transfers', 'xendit_status')) {
                    $table->string('xendit_status')->nullable()->after('xendit_disbursement_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['notification_channel', 'admin_fee', 'xendit_secret_key', 'xendit_webhook_token']);
        });

        if (Schema::hasTable('transfers')) {
            Schema::table('transfers', function (Blueprint $table) {
                $table->dropColumn(['gross_amount', 'admin_fee', 'balance_type', 'finance_reference_id', 'xendit_disbursement_id', 'xendit_status']);
            });
        }
    }
};
