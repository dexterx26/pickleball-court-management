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
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'venue_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('venue_id')->nullable()->after('court_owner_id')->constrained('venues')->nullOnDelete();
            });

            // Backfill owner and assistant users to venue_id = 1
            DB::table('users')
                ->whereIn('role', ['court_owner', 'admin_assistant'])
                ->whereNull('venue_id')
                ->update(['venue_id' => 1]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'venue_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['venue_id']);
                $table->dropColumn('venue_id');
            });
        }
    }
};
