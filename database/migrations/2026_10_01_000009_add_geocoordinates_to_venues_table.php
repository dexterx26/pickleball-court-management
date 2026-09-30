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
        if (Schema::hasTable('venues')) {
            Schema::table('venues', function (Blueprint $table) {
                if (!Schema::hasColumn('venues', 'latitude')) {
                    $table->decimal('latitude', 10, 7)->nullable()->after('country');
                }
                if (!Schema::hasColumn('venues', 'longitude')) {
                    $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Latitude and longitude are part of venues definition
    }
};
