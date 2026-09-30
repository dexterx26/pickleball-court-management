<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\BookingSlot;
use App\Models\Court;
use App\Models\CourtSubscription;
use App\Models\FacilityPhoto;
use App\Models\PlatformSetting;
use App\Models\SubscriptionInvoice;
use App\Models\User;
use App\Models\Venue;
use App\Models\VenueSetting;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Initialize Platform Settings (Singleton)
        PlatformSetting::firstOrCreate(['id' => 1], [
            'subscription_price_monthly' => 300.00,
            'subscription_price_annual' => 3000.00,
            'trial_days' => 14,
            'grace_period_days' => 3,
            'auto_suspend_on_expiry' => true,
            'platform_payment_mode' => 'paymongo',
            'platform_bank_name' => 'BDO / GCash Official',
            'platform_bank_account_name' => 'Pickleball Management Platform',
            'platform_bank_account_number' => '0917-000-PLATFORM',
        ]);

        // 2. Create platform_admin and system admin
        $platformAdmin = User::create([
            'name' => 'Platform Superadmin',
            'email' => 'platform@paddlefield.com',
            'password' => Hash::make('password123'),
            'role' => 'platform_admin',
            'phone' => '+63 917 000 0002',
        ]);

        $admin = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@paddlefield.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'phone' => '+63 917 000 0001',
        ]);

        // 3. Create 3 court_owners
        $owner1 = User::create([
            'name' => 'Allan (Paddle Field Owner)',
            'email' => 'owner@paddlefield.com',
            'password' => Hash::make('password123'),
            'role' => 'court_owner',
            'phone' => '+63 917 555 7233',
        ]);

        $owner2 = User::create([
            'name' => 'Carlos Mendoza (SM Hub Owner)',
            'email' => 'owner2@smhub.com',
            'password' => Hash::make('password123'),
            'role' => 'court_owner',
            'phone' => '+63 918 888 2211',
        ]);

        $owner3 = User::create([
            'name' => 'Grace Santos (Metro Manila Club)',
            'email' => 'owner3@mmpickleball.com',
            'password' => Hash::make('password123'),
            'role' => 'court_owner',
            'phone' => '+63 920 333 4455',
        ]);

        // 4. Create 3 Venues
        $venue1 = Venue::updateOrCreate(['id' => 1], [
            'owner_id' => $owner1->id,
            'slug' => 'paddle-field-sports-center',
            'name' => 'Paddle Field Sports Center',
            'tagline' => 'Premier Indoor & Outdoor Pickleball Facility',
            'description' => 'Welcome to Paddle Field Sports Center — the leading destination for competitive and social pickleball in Central Luzon. Featuring tournament-grade cushion acrylic courts, high-lumen anti-glare LED lighting, luxury player lounge, and automated reservations.',
            'phone' => '+63 917 555 7233',
            'email' => 'info@paddlefieldsports.com',
            'address' => 'Bacal 3, Talavera, Nueva Ecija',
            'city' => 'Talavera',
            'province' => 'Nueva Ecija',
            'country' => 'Philippines',
            'latitude' => 15.5833000,
            'longitude' => 120.9167000,
            'booking_ref_prefix' => 'PF',
            'status' => 'approved',
            'is_active' => true,
            'is_verified' => true,
        ]);
        $owner1->update(['venue_id' => $venue1->id]);

        $venue2 = Venue::updateOrCreate(['slug' => 'sm-pickleball-hub'], [
            'owner_id' => $owner2->id,
            'name' => 'SM Pickleball Hub Cabanatuan',
            'tagline' => 'State-of-the-art Indoor Pickleball Arena',
            'description' => 'Conveniently located at the heart of Cabanatuan City. Fully air-conditioned courts with spectator mezzanine, equipment pro shop, and certified coaching sessions.',
            'phone' => '+63 918 888 2211',
            'email' => 'contact@smpickleballhub.com',
            'address' => 'Maharlika Highway, Cabanatuan City',
            'city' => 'Cabanatuan',
            'province' => 'Nueva Ecija',
            'country' => 'Philippines',
            'latitude' => 15.4859000,
            'longitude' => 120.9673000,
            'booking_ref_prefix' => 'SM',
            'status' => 'approved',
            'is_active' => true,
            'is_verified' => true,
        ]);
        $owner2->update(['venue_id' => $venue2->id]);

        $venue3 = Venue::updateOrCreate(['slug' => 'metro-manila-pickleball'], [
            'owner_id' => $owner3->id,
            'name' => 'Metro Manila Pickleball Club',
            'tagline' => 'Capital City\'s Premier Pickleball Community',
            'description' => 'A vibrant open-play and competitive hub with modern outdoor cushioned hard courts, evening floodlights, coaching clinics, and tournaments.',
            'phone' => '+63 920 333 4455',
            'email' => 'hello@mmpickleball.com',
            'address' => 'Commonwealth Avenue, Diliman, Quezon City',
            'city' => 'Quezon City',
            'province' => 'Metro Manila',
            'country' => 'Philippines',
            'latitude' => 14.6760000,
            'longitude' => 121.0437000,
            'booking_ref_prefix' => 'MM',
            'status' => 'approved',
            'is_active' => true,
            'is_verified' => true,
        ]);
        $owner3->update(['venue_id' => $venue3->id]);

        // 5. Create VenueSettings for each venue
        VenueSetting::updateOrCreate(['venue_id' => $venue1->id], [
            'venue_name' => 'Paddle Field Sports Center',
            'tagline' => 'Premier Indoor & Outdoor Pickleball Facility',
            'description' => 'Welcome to Paddle Field Sports Center. Equipped with tournament-grade cushion acrylic courts, high-lumen anti-glare LED lighting, luxury player lounge, and automated reservations.',
            'phone' => '+63 917 555 7233',
            'email' => 'info@paddlefieldsports.com',
            'address' => 'Bacal 3, Talavera, Nueva Ecija',
            'opening_time' => '06:00',
            'closing_time' => '00:00',
            'currency' => 'PHP',
            'currency_symbol' => '₱',
            'payment_mode' => 'manual_receipt',
            'xendit_simulation_mode' => true,
            'paymongo_simulation_mode' => true,
            'manual_bank_name' => 'GCash / Maya / BDO Online',
            'manual_account_name' => 'Paddle Field Sports Center Inc.',
            'manual_account_number' => '0917-555-7233',
            'manual_payment_instructions' => "1. Send exact booking total via GCash or Maya to 0917-555-7233 or scan the QR standee.\n2. In the payment note, indicate your Booking Reference Code.\n3. Take a screenshot of the completed payment receipt.\n4. Upload the screenshot image below. The owner will review and confirm your slot promptly!",
            'holding_duration_seconds' => 120,
        ]);

        VenueSetting::updateOrCreate(['venue_id' => $venue2->id], [
            'venue_name' => 'SM Pickleball Hub Cabanatuan',
            'tagline' => 'State-of-the-art Indoor Pickleball Arena',
            'description' => 'Fully air-conditioned tournament courts at the heart of Cabanatuan.',
            'phone' => '+63 918 888 2211',
            'email' => 'contact@smpickleballhub.com',
            'address' => 'Maharlika Highway, Cabanatuan City',
            'opening_time' => '07:00',
            'closing_time' => '23:00',
            'currency' => 'PHP',
            'currency_symbol' => '₱',
            'payment_mode' => 'paymongo',
            'xendit_simulation_mode' => true,
            'paymongo_simulation_mode' => true,
            'manual_bank_name' => 'GCash / BPI',
            'manual_account_name' => 'SM Pickleball Hub',
            'manual_account_number' => '0918-888-2211',
            'holding_duration_seconds' => 120,
        ]);

        VenueSetting::updateOrCreate(['venue_id' => $venue3->id], [
            'venue_name' => 'Metro Manila Pickleball Club',
            'tagline' => 'Capital City\'s Premier Pickleball Community',
            'description' => 'Outdoor floodlit courts with community open play.',
            'phone' => '+63 920 333 4455',
            'email' => 'hello@mmpickleball.com',
            'address' => 'Commonwealth Avenue, Diliman, Quezon City',
            'opening_time' => '06:00',
            'closing_time' => '22:00',
            'currency' => 'PHP',
            'currency_symbol' => '₱',
            'payment_mode' => 'manual_receipt',
            'xendit_simulation_mode' => true,
            'paymongo_simulation_mode' => true,
            'manual_bank_name' => 'Maya / UnionBank',
            'manual_account_name' => 'Metro Manila Pickleball Club',
            'manual_account_number' => '0920-333-4455',
            'holding_duration_seconds' => 120,
        ]);

        // 6. Create Admin Assistant for Venue 1
        $assistant = User::create([
            'name' => 'Jane',
            'email' => 'assistant@paddlefield.com',
            'password' => Hash::make('password123'),
            'role' => 'admin_assistant',
            'court_owner_id' => $owner1->id,
            'venue_id' => $venue1->id,
            'permissions' => ['schedule', 'approvals'],
            'is_active' => true,
            'phone' => '+63 919 123 4567',
        ]);

        // 7. Create 20 registered players (role: client)
        $players = [
            ['Michael Austria', 'player@gmail.com', '+63 918 222 3344'],
            ['Jeric Dela Cruz', 'player2@gmail.com', '+63 918 555 6789'],
            ['Paolo Soriano', 'paolo@gmail.com', '+63 918 777 8899'],
            ['Kristine Ramos', 'kristine@gmail.com', '+63 918 101 0001'],
            ['Mark Bautista', 'mark.b@gmail.com', '+63 918 101 0002'],
            ['Camille Lopez', 'camille@gmail.com', '+63 918 101 0003'],
            ['Danilo Tan', 'danilo@gmail.com', '+63 918 101 0004'],
            ['Erika Dizon', 'erika@gmail.com', '+63 918 101 0005'],
            ['Francis Garcia', 'francis@gmail.com', '+63 918 101 0006'],
            ['Glaiza Gomez', 'glaiza@gmail.com', '+63 918 101 0007'],
            ['Harold Pineda', 'harold@gmail.com', '+63 918 101 0008'],
            ['Isabel Castro', 'isabel@gmail.com', '+63 918 101 0009'],
            ['John Rey David', 'johnrey@gmail.com', '+63 918 101 0010'],
            ['Karen Morales', 'karen@gmail.com', '+63 918 101 0011'],
            ['Leo Hernandez', 'leo@gmail.com', '+63 918 101 0012'],
            ['Monique Diaz', 'monique@gmail.com', '+63 918 101 0013'],
            ['Nathaniel Cruz', 'nate@gmail.com', '+63 918 101 0014'],
            ['Olivia Santos', 'olivia@gmail.com', '+63 918 101 0015'],
            ['Patrick Roxas', 'patrick@gmail.com', '+63 918 101 0016'],
            ['Quinnie Flores', 'quinnie@gmail.com', '+63 918 101 0017'],
        ];

        $seededPlayers = [];
        foreach ($players as $p) {
            $seededPlayers[] = User::create([
                'name' => $p[0],
                'email' => $p[1],
                'password' => Hash::make('password123'),
                'role' => 'client',
                'phone' => $p[2],
            ]);
        }

        // 8. Create Courts (3 per venue = 9 courts total)
        $v1Courts = [
            ['Court 1 - Championship Center', 1, 'indoor', 'Pro Cushion Acrylic', 150.00, '06:00', '00:00'],
            ['Court 2 - Pro Indoor', 2, 'indoor', 'Pro Cushion Acrylic', 150.00, '06:00', '00:00'],
            ['Court 3 - Open Air Covered', 3, 'indoor', 'Pro Cushion Acrylic', 150.00, '06:00', '00:00'],
        ];
        $allCourts = [];
        foreach ($v1Courts as $c) {
            $allCourts[] = Court::create([
                'venue_id' => $venue1->id,
                'name' => $c[0],
                'court_number' => $c[1],
                'type' => $c[2],
                'surface_type' => $c[3],
                'price_per_hour' => $c[4],
                'max_players' => 4,
                'opening_time' => $c[5],
                'closing_time' => $c[6],
                'image_path' => 'courts/court-1.jpg',
                'is_active' => true,
            ]);
        }

        $v2Courts = [
            ['Court 1 - Aircon Pro', 1, 'indoor', 'Taraflex Sport Cushion', 250.00, '07:00', '23:00'],
            ['Court 2 - Tournament Arena', 2, 'indoor', 'Taraflex Sport Cushion', 250.00, '07:00', '23:00'],
            ['Court 3 - Training Hub', 3, 'indoor', 'Taraflex Sport Cushion', 200.00, '07:00', '23:00'],
        ];
        foreach ($v2Courts as $c) {
            $allCourts[] = Court::create([
                'venue_id' => $venue2->id,
                'name' => $c[0],
                'court_number' => $c[1],
                'type' => $c[2],
                'surface_type' => $c[3],
                'price_per_hour' => $c[4],
                'max_players' => 4,
                'opening_time' => $c[5],
                'closing_time' => $c[6],
                'image_path' => 'courts/court-2.jpg',
                'is_active' => true,
            ]);
        }

        $v3Courts = [
            ['Court A - Metro Center', 1, 'outdoor', 'Hard Acrylic All-Weather', 180.00, '06:00', '22:00'],
            ['Court B - Sunset Court', 2, 'outdoor', 'Hard Acrylic All-Weather', 180.00, '06:00', '22:00'],
            ['Court C - Community Play', 3, 'outdoor', 'Hard Acrylic All-Weather', 150.00, '06:00', '22:00'],
        ];
        foreach ($v3Courts as $c) {
            $allCourts[] = Court::create([
                'venue_id' => $venue3->id,
                'name' => $c[0],
                'court_number' => $c[1],
                'type' => $c[2],
                'surface_type' => $c[3],
                'price_per_hour' => $c[4],
                'max_players' => 4,
                'opening_time' => $c[5],
                'closing_time' => $c[6],
                'image_path' => 'courts/court-1.jpg',
                'is_active' => true,
            ]);
        }

        // 9. Create Court Subscriptions & Invoices
        foreach ($allCourts as $idx => $court) {
            $ownerId = $court->venue->owner_id;
            // Make some active, some trial for realism
            $isTrial = ($idx % 3 === 2);
            $sub = CourtSubscription::create([
                'court_id' => $court->id,
                'owner_id' => $ownerId,
                'venue_id' => $court->venue_id,
                'billing_cycle' => 'monthly',
                'price_snapshot' => 300.00,
                'status' => $isTrial ? 'trial' : 'active',
                'trial_ends_at' => $isTrial ? now()->addDays(14) : null,
                'starts_at' => now()->subDays(10),
                'ends_at' => $isTrial ? now()->addDays(14) : now()->addDays(20),
            ]);

            if (!$isTrial) {
                SubscriptionInvoice::create([
                    'subscription_id' => $sub->id,
                    'owner_id' => $ownerId,
                    'court_id' => $court->id,
                    'invoice_number' => sprintf('INV-%s-%04d', date('Y'), $idx + 1),
                    'amount' => 300.00,
                    'billing_cycle' => 'monthly',
                    'status' => 'paid',
                    'payment_method' => 'paymongo',
                    'paid_at' => now()->subDays(10),
                    'period_start' => now()->subDays(10)->toDateString(),
                    'period_end' => now()->addDays(20)->toDateString(),
                ]);
            }
        }

        // 10. Seed Facility Photos
        $photos = [
            [$venue1->id, 'Championship Court 1 in Action', 'court', 'gallery/court-action.jpg', 'Pro-grade tournament match play', 1],
            [$venue1->id, 'Executive Player Lounge', 'lounge', 'gallery/club-lounge.jpg', 'Relax and hydrate with complimentary refreshment', 2],
            [$venue1->id, 'Evening Play Under Anti-Glare LEDs', 'court', 'gallery/outdoor-sunset.jpg', 'Crisp visibility during peak night sessions', 3],
            [$venue2->id, 'SM Hub Mezzanine View', 'court', 'gallery/court-action.jpg', 'Panoramic viewing deck for spectators', 1],
            [$venue3->id, 'Metro Sunset Tournament', 'court', 'gallery/outdoor-sunset.jpg', 'Central Manila weekend doubles open', 1],
        ];

        foreach ($photos as $p) {
            FacilityPhoto::create([
                'venue_id' => $p[0],
                'title' => $p[1],
                'category' => $p[2],
                'image_path' => $p[3],
                'caption' => $p[4],
                'sort_order' => $p[5],
                'is_featured' => true,
            ]);
        }

        // 11. Seed sample reservations on Venue 1 Court 1
        $today = Carbon::today()->format('Y-m-d');
        $b1 = Booking::create([
            'venue_id' => $venue1->id,
            'booking_reference' => 'PF-' . date('Ymd') . '-SAM01',
            'user_id' => $seededPlayers[0]->id,
            'customer_name' => $seededPlayers[0]->name,
            'customer_phone' => $seededPlayers[0]->phone,
            'customer_email' => $seededPlayers[0]->email,
            'court_id' => $allCourts[0]->id,
            'booking_date' => $today,
            'start_time' => '14:00',
            'end_time' => '16:00',
            'total_hours' => 2,
            'rate_per_hour' => 150.00,
            'total_amount' => 300.00,
            'players_count' => 4,
            'payment_method' => 'manual_receipt',
            'payment_status' => 'paid',
            'booking_status' => 'confirmed',
            'approved_by' => $owner1->id,
            'approved_at' => now(),
        ]);

        BookingSlot::create([
            'venue_id' => $venue1->id,
            'booking_id' => $b1->id,
            'court_id' => $allCourts[0]->id,
            'date' => $today,
            'slot_time' => '14:00',
            'rate_per_hour' => 150.00,
            'status' => 'confirmed',
        ]);
        BookingSlot::create([
            'venue_id' => $venue1->id,
            'booking_id' => $b1->id,
            'court_id' => $allCourts[0]->id,
            'date' => $today,
            'slot_time' => '15:00',
            'rate_per_hour' => 150.00,
            'status' => 'confirmed',
        ]);
    }
}
