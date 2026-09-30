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
        if (!Schema::hasTable('subscription_invoices')) {
            Schema::create('subscription_invoices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('subscription_id')->nullable()->constrained('court_subscriptions')->nullOnDelete();
                $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('court_id')->nullable()->constrained('courts')->nullOnDelete();
                $table->string('invoice_number', 50)->unique();
                $table->decimal('amount', 10, 2);
                $table->string('billing_cycle', 20)->default('monthly');
                $table->string('status', 30)->default('paid'); // unpaid, paid, refunded
                $table->string('payment_method', 50)->nullable();
                $table->string('paymongo_checkout_id', 200)->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->date('period_start')->nullable();
                $table->date('period_end')->nullable();
                $table->string('receipt_path', 500)->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_invoices');
    }
};
