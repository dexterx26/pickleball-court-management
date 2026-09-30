<?php

namespace Tests\Feature;

use App\Models\Court;
use App\Models\CourtSubscription;
use App\Models\PlatformSetting;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformAdminAndSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    protected User $platformAdmin;
    protected User $owner;
    protected Venue $venue;
    protected Court $court;

    protected function setUp(): void
    {
        parent::setUp();

        $this->platformAdmin = User::factory()->create([
            'role' => 'platform_admin',
            'email' => 'superadmin@pickleball.com',
            'is_active' => true,
        ]);

        $this->owner = User::factory()->create([
            'role' => 'court_owner',
            'email' => 'court_owner@example.com',
            'is_active' => true,
        ]);

        $this->venue = Venue::create([
            'owner_id' => $this->owner->id,
            'name' => 'Pending Smash Arena',
            'slug' => 'pending-smash-arena',
            'prefix' => 'PSA',
            'address' => '123 Sport Blvd',
            'city' => 'Pasig',
            'province' => 'Metro Manila',
            'latitude' => 14.5800,
            'longitude' => 121.0600,
            'status' => 'pending',
            'is_active' => false,
        ]);

        $this->owner->update(['venue_id' => $this->venue->id]);

        $this->court = Court::create([
            'venue_id' => $this->venue->id,
            'court_number' => 1,
            'name' => 'Court 1',
            'price_per_hour' => 300,
            'max_players' => 4,
            'is_active' => true,
        ]);

        CourtSubscription::create([
            'venue_id' => $this->venue->id,
            'court_id' => $this->court->id,
            'plan' => 'monthly',
            'price' => 300,
            'status' => 'trial',
            'starts_at' => now(),
            'ends_at' => now()->addDays(14),
        ]);
    }

    public function test_platform_admin_can_access_venues_directory()
    {
        $response = $this->actingAs($this->platformAdmin)->get(route('platform.venues'));
        $response->assertStatus(200);
        $response->assertSee('Pending Smash Arena');
        $response->assertSee('Pending Review');
    }

    public function test_platform_admin_can_approve_pending_venue()
    {
        $response = $this->actingAs($this->platformAdmin)
            ->post(route('platform.venues.approve', $this->venue->id));

        $response->assertRedirect();
        $this->venue->refresh();

        $this->assertEquals('approved', $this->venue->status);
        $this->assertTrue($this->venue->is_active);
    }

    public function test_platform_admin_can_reject_pending_venue_with_reason()
    {
        $response = $this->actingAs($this->platformAdmin)
            ->post(route('platform.venues.reject', $this->venue->id), [
                'reason' => 'Address could not be verified by platform operations.',
            ]);

        $response->assertRedirect();
        $this->venue->refresh();

        $this->assertEquals('rejected', $this->venue->status);
        $this->assertFalse($this->venue->is_active);
        $this->assertEquals('Address could not be verified by platform operations.', $this->venue->rejection_reason);
    }

    public function test_platform_admin_can_view_and_update_subscription_pricing()
    {
        $response = $this->actingAs($this->platformAdmin)->get(route('platform.subscriptions'));
        $response->assertStatus(200);
        $response->assertSee('Subscription Plans');

        $updateResponse = $this->actingAs($this->platformAdmin)
            ->post(route('platform.subscriptions.settings'), [
                'monthly_price_per_court' => 350,
                'annual_price_per_court' => 3500,
                'trial_period_days' => 21,
                'grace_period_days' => 5,
            ]);

        $updateResponse->assertRedirect();

        $settings = PlatformSetting::getSettings();
        $this->assertEquals(350, $settings->monthly_price_per_court);
        $this->assertEquals(3500, $settings->annual_price_per_court);
        $this->assertEquals(21, $settings->trial_period_days);
        $this->assertEquals(5, $settings->grace_period_days);
    }

    public function test_court_owner_can_view_subscriptions_and_renew()
    {
        $response = $this->actingAs($this->owner)->get(route('owner.subscriptions'));
        $response->assertStatus(200);
        $response->assertSee('Court 1');
        $response->assertSee('Free Trial');

        // Renew court with annual plan
        $renewResponse = $this->actingAs($this->owner)
            ->post(route('owner.subscriptions.renew', $this->court->id), [
                'plan' => 'annual',
            ]);

        $renewResponse->assertRedirect();

        $this->assertDatabaseHas('court_subscriptions', [
            'court_id' => $this->court->id,
            'billing_cycle' => 'annual',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('subscription_invoices', [
            'subscription_id' => $this->court->subscriptions()->latest('id')->value('id'),
            'status' => 'paid',
        ]);
    }

    public function test_regular_player_is_forbidden_from_platform_admin_portal()
    {
        $player = User::factory()->create([
            'role' => 'client',
            'is_active' => true,
        ]);

        $response = $this->actingAs($player)->get(route('platform.venues'));
        $response->assertStatus(403);
    }
}
