<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Court;
use App\Models\User;
use App\Models\Venue;
use App\Models\VenueSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiTenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected User $ownerA;
    protected Venue $venueA;
    protected Court $courtA;

    protected User $ownerB;
    protected Venue $venueB;
    protected Court $courtB;

    protected function setUp(): void
    {
        parent::setUp();

        // Venue A
        $this->ownerA = User::factory()->create([
            'role' => 'court_owner',
            'email' => 'owner_a@example.com',
            'is_active' => true,
        ]);

        $this->venueA = Venue::create([
            'owner_id' => $this->ownerA->id,
            'name' => 'Venue Alpha',
            'slug' => 'venue-alpha',
            'prefix' => 'VNA',
            'status' => 'approved',
            'is_active' => true,
        ]);

        $this->ownerA->update(['venue_id' => $this->venueA->id]);

        $this->courtA = Court::create([
            'venue_id' => $this->venueA->id,
            'court_number' => 1,
            'name' => 'Alpha Court 1',
            'price_per_hour' => 300,
            'max_players' => 4,
            'is_active' => true,
        ]);

        VenueSetting::create([
            'venue_id' => $this->venueA->id,
            'payment_mode' => 'manual_receipt',
            'opening_time' => '06:00',
            'closing_time' => '22:00',
        ]);

        // Venue B
        $this->ownerB = User::factory()->create([
            'role' => 'court_owner',
            'email' => 'owner_b@example.com',
            'is_active' => true,
        ]);

        $this->venueB = Venue::create([
            'owner_id' => $this->ownerB->id,
            'name' => 'Venue Beta',
            'slug' => 'venue-beta',
            'prefix' => 'VNB',
            'status' => 'approved',
            'is_active' => true,
        ]);

        $this->ownerB->update(['venue_id' => $this->venueB->id]);

        $this->courtB = Court::create([
            'venue_id' => $this->venueB->id,
            'court_number' => 1,
            'name' => 'Beta Court 1',
            'price_per_hour' => 400,
            'max_players' => 4,
            'is_active' => true,
        ]);

        VenueSetting::create([
            'venue_id' => $this->venueB->id,
            'payment_mode' => 'paymongo',
            'opening_time' => '07:00',
            'closing_time' => '23:00',
        ]);
    }

    public function test_owner_only_sees_their_own_courts_in_courts_management()
    {
        $response = $this->actingAs($this->ownerA)->get(route('owner.courts.index'));
        $response->assertStatus(200);
        $response->assertSee('Alpha Court 1');
        $response->assertDontSee('Beta Court 1');

        $responseB = $this->actingAs($this->ownerB)->get(route('owner.courts.index'));
        $responseB->assertStatus(200);
        $responseB->assertSee('Beta Court 1');
        $responseB->assertDontSee('Alpha Court 1');
    }

    public function test_owner_cannot_update_or_delete_court_from_another_venue()
    {
        // Owner A attempts to delete Court B
        $response = $this->actingAs($this->ownerA)->delete(route('owner.courts.destroy', $this->courtB->id));
        $response->assertStatus(404);

        // Verify Court B still exists and is untouched
        $this->assertDatabaseHas('courts', [
            'id' => $this->courtB->id,
            'is_active' => true,
            'deleted_at' => null,
        ]);
    }

    public function test_owner_dashboard_and_ledger_only_displays_own_venue_bookings()
    {
        $today = now()->format('Y-m-d');

        // Booking at Venue A
        $bookingA = Booking::create([
            'venue_id' => $this->venueA->id,
            'court_id' => $this->courtA->id,
            'booking_reference' => 'VNA-BOOKING-001',
            'customer_name' => 'Alpha Customer',
            'customer_phone' => '09170000001',
            'booking_date' => $today,
            'start_time' => '08:00',
            'end_time' => '09:00',
            'total_hours' => 1,
            'rate_per_hour' => 300,
            'total_amount' => 300,
            'payment_method' => 'manual_receipt',
            'payment_status' => 'paid',
            'booking_status' => 'confirmed',
        ]);

        // Booking at Venue B
        $bookingB = Booking::create([
            'venue_id' => $this->venueB->id,
            'court_id' => $this->courtB->id,
            'booking_reference' => 'VNB-BOOKING-001',
            'customer_name' => 'Beta Customer',
            'customer_phone' => '09170000002',
            'booking_date' => $today,
            'start_time' => '10:00',
            'end_time' => '11:00',
            'total_hours' => 1,
            'rate_per_hour' => 400,
            'total_amount' => 400,
            'payment_method' => 'paymongo',
            'payment_status' => 'paid',
            'booking_status' => 'confirmed',
        ]);

        // Check Owner A ledger
        $responseA = $this->actingAs($this->ownerA)->get(route('owner.bookings.index'));
        $responseA->assertStatus(200);
        $responseA->assertSee('VNA-BOOKING-001');
        $responseA->assertDontSee('VNB-BOOKING-001');

        // Check Owner B ledger
        $responseB = $this->actingAs($this->ownerB)->get(route('owner.bookings.index'));
        $responseB->assertStatus(200);
        $responseB->assertSee('VNB-BOOKING-001');
        $responseB->assertDontSee('VNA-BOOKING-001');
    }
}
