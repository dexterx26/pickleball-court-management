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
        if (!Schema::hasTable('venues')) {
            Schema::create('venues', function (Blueprint $table) {
                $table->id();
                $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('slug', 100)->unique();
                $table->string('name', 150);
                $table->string('tagline', 255)->nullable();
                $table->text('description')->nullable();
                $table->string('phone', 30)->nullable();
                $table->string('email', 100)->nullable();
                $table->string('address', 500)->nullable();
                $table->string('city', 100)->nullable();
                $table->string('province', 100)->nullable();
                $table->string('country', 100)->default('Philippines');
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->string('logo_path', 500)->nullable();
                $table->string('cover_image_path', 500)->nullable();
                $table->string('booking_ref_prefix', 10)->default('PF');
                $table->string('status', 50)->default('approved'); // pending_approval, approved, rejected, suspended
                $table->boolean('is_active')->default(true);
                $table->boolean('is_verified')->default(false);
                $table->text('rejection_reason')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // Backfill step: insert default venue 1 if not exists
        if (DB::table('venues')->where('id', 1)->doesntExist()) {
            $existing = Schema::hasTable('venue_settings') ? DB::table('venue_settings')->first() : null;
            $ownerId = Schema::hasTable('users')
                ? (DB::table('users')->where('role', 'court_owner')->value('id') ?? DB::table('users')->value('id'))
                : null;

            DB::table('venues')->insert([
                'id' => 1,
                'owner_id' => $ownerId,
                'slug' => 'paddle-field-sports-center',
                'name' => $existing->venue_name ?? 'Paddle Field Sports Center',
                'tagline' => $existing->tagline ?? 'Premier Indoor & Outdoor Pickleball Facility',
                'description' => $existing->description ?? 'Experience world-class pickleball action at Paddle Field Sports Center. Equipped with tournament-grade cushioned acrylic courts, high-powered LED lighting, and premium club amenities.',
                'phone' => $existing->phone ?? '+63 917 555 7233',
                'email' => $existing->email ?? 'contact@paddlefield.com',
                'address' => $existing->address ?? 'Bacal 3, Talavera, Nueva Ecija',
                'city' => 'Talavera',
                'province' => 'Nueva Ecija',
                'country' => 'Philippines',
                'latitude' => 15.5833000,
                'longitude' => 120.9167000,
                'booking_ref_prefix' => 'PF',
                'status' => 'approved',
                'is_active' => true,
                'is_verified' => true,
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
        Schema::dropIfExists('venues');
    }
};
