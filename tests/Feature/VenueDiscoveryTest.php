<?php

namespace Tests\Feature;

use App\Models\Court;
use App\Models\User;
use App\Models\Venue;
use App\Models\VenueSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VenueDiscoveryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $owner = User::factory()->create([
            'role' => 'court_owner',
            'is_active' => true,
        ]);

        // Venue 1: BGC Manila (14.5547, 121.0509)
        $venue1 = Venue::create([
            'owner_id' => $owner->id,
            'name' => 'Metro Smash BGC',
            'slug' => 'metro-smash-bgc',
            'prefix' => 'BGC',
            'address' => '7th Ave, BGC',
            'city' => 'Taguig',
            'province' => 'Metro Manila',
            'latitude' => 14.5547,
            'longitude' => 121.0509,
            'status' => 'approved',
            'is_active' => true,
        ]);

        Court::create([
            'venue_id' => $venue1->id,
            'court_number' => 1,
            'name' => 'Center Court',
            'price_per_hour' => 350,
            'max_players' => 4,
            'is_active' => true,
        ]);

        VenueSetting::create([
            'venue_id' => $venue1->id,
            'payment_mode' => 'manual_receipt',
            'opening_time' => '06:00',
            'closing_time' => '22:00',
            'slot_duration_minutes' => 60,
            'hold_timer_seconds' => 120,
        ]);

        // Venue 2: Makati (14.5547, 121.0244 - ~3km away)
        $venue2 = Venue::create([
            'owner_id' => $owner->id,
            'name' => 'Poblacion Pickleball Arena',
            'slug' => 'poblacion-arena',
            'prefix' => 'POB',
            'address' => 'Burgos St, Poblacion',
            'city' => 'Makati',
            'province' => 'Metro Manila',
            'latitude' => 14.5547,
            'longitude' => 121.0244,
            'status' => 'approved',
            'is_active' => true,
        ]);

        Court::create([
            'venue_id' => $venue2->id,
            'court_number' => 1,
            'name' => 'Arena 1',
            'price_per_hour' => 400,
            'max_players' => 4,
            'is_active' => true,
        ]);

        VenueSetting::create([
            'venue_id' => $venue2->id,
            'payment_mode' => 'paymongo',
            'opening_time' => '07:00',
            'closing_time' => '23:00',
            'slot_duration_minutes' => 60,
            'hold_timer_seconds' => 120,
        ]);

        // Venue 3: Pending Approval (Should not show in active discovery)
        Venue::create([
            'owner_id' => $owner->id,
            'name' => 'Unapproved Venue',
            'slug' => 'unapproved-venue',
            'prefix' => 'UNAP',
            'address' => 'Some address',
            'city' => 'Taguig',
            'province' => 'Metro Manila',
            'status' => 'pending',
            'is_active' => false,
        ]);
    }

    public function test_public_venues_discovery_page_loads_approved_venues()
    {
        $response = $this->get('/venues');
        $response->assertStatus(200);
        $response->assertSee('Metro Smash BGC');
        $response->assertSee('Poblacion Pickleball Arena');
        $response->assertDontSee('Unapproved Venue');
    }

    public function test_venue_search_by_keyword_and_city()
    {
        $response = $this->get('/venues?search=Metro');
        $response->assertStatus(200);
        $response->assertSee('Metro Smash BGC');
        $response->assertDontSee('Poblacion Pickleball Arena');

        $cityResponse = $this->get('/venues?city=Makati');
        $cityResponse->assertStatus(200);
        $cityResponse->assertSee('Poblacion Pickleball Arena');
        $cityResponse->assertDontSee('Metro Smash BGC');
    }

    public function test_venue_proximity_search_api()
    {
        // Coordinate close to BGC (14.5540, 121.0500)
        $response = $this->getJson('/api/venues/search?lat=14.5540&lng=121.0500');
        $response->assertStatus(200);
        $data = $response->json();

        $this->assertTrue($data['success']);
        $this->assertGreaterThanOrEqual(2, count($data['venues']));
        $this->assertEquals('metro-smash-bgc', $data['venues'][0]['slug']);
        $this->assertLessThan($data['venues'][1]['distance_km'], $data['venues'][0]['distance_km']);
    }

    public function test_venue_detail_and_dedicated_booking_page()
    {
        $response = $this->get('/venues/metro-smash-bgc');
        $response->assertStatus(200);
        $response->assertSee('Metro Smash BGC');
        $response->assertSee('Center Court');
        $response->assertSee('₱350 / hr');
    }

    public function test_venue_specific_availability_api()
    {
        $today = now()->format('Y-m-d');
        $response = $this->getJson("/api/venues/metro-smash-bgc/availability?date={$today}");
        $response->assertStatus(200);
        $data = $response->json();

        $this->assertTrue($data['success']);
        $this->assertCount(1, $data['courts']);
        $this->assertEquals('Center Court', $data['courts'][0]['court']['name']);
    }
}
