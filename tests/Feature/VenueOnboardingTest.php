<?php

namespace Tests\Feature;

use App\Models\Court;
use App\Models\CourtSubscription;
use App\Models\User;
use App\Models\Venue;
use App\Models\VenueSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VenueOnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_court_owner_can_complete_5_step_onboarding_wizard(): void
    {
        // 1. Register a new court owner
        $owner = User::create([
            'name' => 'John Pickleball',
            'email' => 'john@pickleballcourts.com',
            'phone' => '+63 917 111 2222',
            'password' => bcrypt('password123'),
            'role' => 'court_owner',
            'venue_id' => null,
        ]);

        // 2. Initial visit to /owner redirects to /owner/onboard/step/1
        $response = $this->actingAs($owner)->get('/owner');
        $response->assertRedirect(route('owner.onboard.step1'));

        // 3. Step 1: Venue Information
        $step1Resp = $this->actingAs($owner)->get(route('owner.onboard.step1'));
        $step1Resp->assertStatus(200);
        $step1Resp->assertSee('Venue Information');

        $post1 = $this->actingAs($owner)->post(route('owner.onboard.post_step1'), [
            'name' => 'Ace Pickleball Club',
            'tagline' => 'Premier Courts in Clark',
            'booking_ref_prefix' => 'ACE',
            'phone' => '+63 917 111 2222',
            'email' => 'contact@acepickleball.com',
            'address' => 'Manuel Roxas Highway',
            'city' => 'Clark Freeport Zone',
            'province' => 'Pampanga',
            'description' => 'Olympic standard pickleball courts in Central Luzon.',
        ]);
        $post1->assertRedirect(route('owner.onboard.step2'));

        // 4. Step 2: Location Geocoordinates
        $step2Resp = $this->actingAs($owner)->get(route('owner.onboard.step2'));
        $step2Resp->assertStatus(200);
        $step2Resp->assertSee('Location Pin');

        $post2 = $this->actingAs($owner)->post(route('owner.onboard.post_step2'), [
            'latitude' => 15.1764,
            'longitude' => 120.5364,
        ]);
        $post2->assertRedirect(route('owner.onboard.step3'));

        // 5. Step 3: Add First Court
        $step3Resp = $this->actingAs($owner)->get(route('owner.onboard.step3'));
        $step3Resp->assertStatus(200);
        $step3Resp->assertSee('Set Up Your First Court');

        $post3 = $this->actingAs($owner)->post(route('owner.onboard.post_step3'), [
            'court_name' => 'Court 1 - Grand Arena',
            'type' => 'indoor',
            'surface_type' => 'Pro Cushion Acrylic',
            'price_per_hour' => 200.00,
            'max_players' => 4,
            'opening_time' => '06:00',
            'closing_time' => '23:00',
        ]);
        $post3->assertRedirect(route('owner.onboard.step4'));

        // 6. Step 4: Payment Setup
        $step4Resp = $this->actingAs($owner)->get(route('owner.onboard.step4'));
        $step4Resp->assertStatus(200);
        $step4Resp->assertSee('Payment Gateway');

        $post4 = $this->actingAs($owner)->post(route('owner.onboard.post_step4'), [
            'payment_mode' => 'manual_receipt',
            'manual_bank_name' => 'GCash',
            'manual_account_name' => 'Ace Sports Management Inc',
            'manual_account_number' => '0917-111-2222',
            'manual_payment_instructions' => 'Send payment to 0917-111-2222 and upload screenshot.',
            'holding_duration_seconds' => 180,
        ]);
        $post4->assertRedirect(route('owner.onboard.step5'));

        // 7. Step 5: Review & Submit
        $step5Resp = $this->actingAs($owner)->get(route('owner.onboard.step5'));
        $step5Resp->assertStatus(200);
        $step5Resp->assertSee('Ace Pickleball Club');
        $step5Resp->assertSee('Court 1 - Grand Arena');

        $submitResp = $this->actingAs($owner)->post(route('owner.onboard.submit'), [
            'agree_terms' => '1',
        ]);
        $submitResp->assertRedirect(route('owner.onboard.waiting'));

        // 8. Verify Database Records
        $venue = Venue::where('slug', 'ace-pickleball-club')->first();
        $this->assertNotNull($venue);
        $this->assertEquals($owner->id, $venue->owner_id);
        $this->assertEquals('pending_approval', $venue->status);
        $this->assertFalse($venue->is_active);
        $this->assertEquals('ACE', $venue->booking_ref_prefix);
        $this->assertEquals(15.1764, (float)$venue->latitude);
        $this->assertEquals(120.5364, (float)$venue->longitude);

        // Verify VenueSetting auto-created
        $setting = VenueSetting::where('venue_id', $venue->id)->first();
        $this->assertNotNull($setting);
        $this->assertEquals('manual_receipt', $setting->payment_mode);
        $this->assertEquals(180, $setting->holding_duration_seconds);

        // Verify First Court
        $court = Court::where('venue_id', $venue->id)->first();
        $this->assertNotNull($court);
        $this->assertEquals('Court 1 - Grand Arena', $court->name);
        $this->assertEquals(200.00, (float)$court->price_per_hour);

        // Verify Trial Subscription created
        $sub = CourtSubscription::where('court_id', $court->id)->first();
        $this->assertNotNull($sub);
        $this->assertEquals('trial', $sub->status);
        $this->assertTrue($sub->isInTrial());
        $this->assertTrue($court->isBookable());

        // Verify Owner venue_id synced
        $this->assertEquals($venue->id, $owner->fresh()->venue_id);

        // 9. Waiting Screen Renders
        $waitingResp = $this->actingAs($owner)->get(route('owner.onboard.waiting'));
        $waitingResp->assertStatus(200);
        $waitingResp->assertSee('Ace Pickleball Club is Under Review');
    }
}
