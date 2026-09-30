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
        if (Schema::hasTable('venue_settings') && !Schema::hasColumn('venue_settings', 'venue_id')) {
            Schema::table('venue_settings', function (Blueprint $table) {
                $table->foreignId('venue_id')->nullable()->after('id')->constrained('venues')->cascadeOnDelete();
            });

            // Backfill existing rows with venue_id = 1
            DB::table('venue_settings')->whereNull('venue_id')->update(['venue_id' => 1]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('venue_settings') && Schema::hasColumn('venue_settings', 'venue_id')) {
            Schema::table('venue_settings', function (Blueprint $table) {
                $table->dropForeign(['venue_id']);
                $table->dropColumn('venue_id');
            });
        }
    }
};
