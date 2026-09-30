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
        if (!Schema::hasTable('platform_settings')) {
            Schema::create('platform_settings', function (Blueprint $table) {
                $table->id();
                $table->decimal('subscription_price_monthly', 10, 2)->default(300.00);
                $table->decimal('subscription_price_annual', 10, 2)->default(3000.00);
                $table->integer('trial_days')->default(14);
                $table->integer('grace_period_days')->default(3);
                $table->boolean('auto_suspend_on_expiry')->default(true);
                $table->string('platform_payment_mode', 50)->default('paymongo');
                $table->string('platform_paymongo_secret_key', 200)->nullable();
                $table->string('platform_paymongo_public_key', 200)->nullable();
                $table->string('platform_bank_name', 150)->nullable()->default('BDO / GCash Official');
                $table->string('platform_bank_account_name', 150)->nullable()->default('Pickleball Management Platform');
                $table->string('platform_bank_account_number', 100)->nullable()->default('0917-000-PLATFORM');
                $table->timestamps();
            });

            DB::table('platform_settings')->insert([
                'id' => 1,
                'subscription_price_monthly' => 300.00,
                'subscription_price_annual' => 3000.00,
                'trial_days' => 14,
                'grace_period_days' => 3,
                'auto_suspend_on_expiry' => true,
                'platform_payment_mode' => 'paymongo',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_settings');
    }
};
