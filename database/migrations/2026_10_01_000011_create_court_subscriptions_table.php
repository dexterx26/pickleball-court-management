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
        if (!Schema::hasTable('court_subscriptions')) {
            Schema::create('court_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('court_id')->constrained('courts')->cascadeOnDelete();
                $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('venue_id')->constrained('venues')->cascadeOnDelete();
                $table->string('billing_cycle', 20)->default('monthly'); // monthly, annual
                $table->decimal('price_snapshot', 10, 2)->default(300.00);
                $table->string('status', 30)->default('active'); // trial, active, expired, suspended, cancelled
                $table->timestamp('trial_ends_at')->nullable();
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->timestamps();

                $table->index(['court_id', 'status']);
                $table->index(['venue_id', 'status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('court_subscriptions');
    }
};
