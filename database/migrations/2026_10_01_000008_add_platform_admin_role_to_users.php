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
        // Update any existing admin role to platform_admin
        if (Schema::hasTable('users')) {
            DB::table('users')->where('role', 'admin')->update(['role' => 'platform_admin']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users')) {
            DB::table('users')->where('role', 'platform_admin')->update(['role' => 'admin']);
        }
    }
};
