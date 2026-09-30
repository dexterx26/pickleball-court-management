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
        if (Schema::hasTable('booking_slots') && !Schema::hasColumn('booking_slots', 'venue_id')) {
            Schema::table('booking_slots', function (Blueprint $table) {
                $table->foreignId('venue_id')->default(1)->after('id')->constrained('venues')->cascadeOnDelete();
                $table->index(['venue_id', 'court_id', 'date', 'slot_time', 'status'], 'venue_court_date_slot_idx');
            });

            DB::table('booking_slots')->whereNull('venue_id')->update(['venue_id' => 1]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('booking_slots') && Schema::hasColumn('booking_slots', 'venue_id')) {
            Schema::table('booking_slots', function (Blueprint $table) {
                $table->dropIndex('venue_court_date_slot_idx');
                $table->dropForeign(['venue_id']);
                $table->dropColumn('venue_id');
            });
        }
    }
};
