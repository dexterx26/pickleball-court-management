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
        if (Schema::hasTable('bookings') && !Schema::hasColumn('bookings', 'venue_id')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->foreignId('venue_id')->default(1)->after('id')->constrained('venues')->cascadeOnDelete();
            });

            DB::table('bookings')->whereNull('venue_id')->update(['venue_id' => 1]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('bookings') && Schema::hasColumn('bookings', 'venue_id')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropForeign(['venue_id']);
                $table->dropColumn('venue_id');
            });
        }
    }
};
