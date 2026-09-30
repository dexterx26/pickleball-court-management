<?php

namespace App\Http\Controllers;

use App\Models\Court;
use App\Models\CourtSubscription;
use App\Models\PlatformSetting;
use App\Models\Venue;
use App\Models\VenueSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OnboardingController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->isOwner()) {
            return redirect()->route('login');
        }

        // Check if user already has a venue
        $venue = $user->venue ?? $user->ownedVenues()->latest()->first();
        if ($venue) {
            if ($venue->status === 'pending_approval') {
                return redirect()->route('owner.onboard.waiting');
            }
            return redirect()->route('owner.dashboard');
        }

        return redirect()->route('owner.onboard.step1');
    }

    public function showStep1(Request $request)
    {
        $onboarding = $request->session()->get('onboarding', []);
        return view('owner.onboard.step1', compact('onboarding'));
    }

    public function postStep1(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'tagline' => 'nullable|string|max:255',
            'booking_ref_prefix' => 'required|string|min:2|max:5|regex:/^[A-Z0-9]+$/i',
            'phone' => 'required|string|max:30',
            'email' => 'required|email|max:100',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $onboarding = $request->session()->get('onboarding', []);

        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('venues/logos', 'public');
            $data['logo_path'] = $logoPath;
            try {
                @copy(storage_path('app/public/' . $logoPath), public_path('storage/' . $logoPath));
            } catch (\Throwable $e) {}
        } elseif (isset($onboarding['step1']['logo_path'])) {
            $data['logo_path'] = $onboarding['step1']['logo_path'];
        }

        if ($request->hasFile('cover_image')) {
            $coverPath = $request->file('cover_image')->store('venues/covers', 'public');
            $data['cover_image_path'] = $coverPath;
            try {
                @copy(storage_path('app/public/' . $coverPath), public_path('storage/' . $coverPath));
            } catch (\Throwable $e) {}
        } elseif (isset($onboarding['step1']['cover_image_path'])) {
            $data['cover_image_path'] = $onboarding['step1']['cover_image_path'];
        }

        $data['booking_ref_prefix'] = strtoupper($data['booking_ref_prefix']);
        $onboarding['step1'] = $data;
        $request->session()->put('onboarding', $onboarding);

        return redirect()->route('owner.onboard.step2');
    }

    public function showStep2(Request $request)
    {
        $onboarding = $request->session()->get('onboarding', []);
        return view('owner.onboard.step2', compact('onboarding'));
    }

    public function postStep2(Request $request)
    {
        $data = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $onboarding = $request->session()->get('onboarding', []);
        $onboarding['step2'] = $data;
        $request->session()->put('onboarding', $onboarding);

        return redirect()->route('owner.onboard.step3');
    }

    public function showStep3(Request $request)
    {
        $onboarding = $request->session()->get('onboarding', []);
        return view('owner.onboard.step3', compact('onboarding'));
    }

    public function postStep3(Request $request)
    {
        $data = $request->validate([
            'court_name' => 'required|string|max:100',
            'type' => 'required|in:indoor,outdoor',
            'surface_type' => 'required|string|max:100',
            'price_per_hour' => 'required|numeric|min:1',
            'max_players' => 'required|integer|min:2|max:10',
            'opening_time' => 'required|string|max:5',
            'closing_time' => 'required|string|max:5',
        ]);

        $onboarding = $request->session()->get('onboarding', []);
        $onboarding['step3'] = $data;
        $request->session()->put('onboarding', $onboarding);

        return redirect()->route('owner.onboard.step4');
    }

    public function showStep4(Request $request)
    {
        $onboarding = $request->session()->get('onboarding', []);
        return view('owner.onboard.step4', compact('onboarding'));
    }

    public function postStep4(Request $request)
    {
        $data = $request->validate([
            'payment_mode' => 'required|in:manual_receipt,paymongo,xendit',
            'manual_bank_name' => 'nullable|string|max:150',
            'manual_account_name' => 'nullable|string|max:150',
            'manual_account_number' => 'nullable|string|max:100',
            'manual_payment_instructions' => 'nullable|string|max:1000',
            'manual_payment_qr' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'holding_duration_seconds' => 'nullable|integer|min:60|max:600',
        ]);

        $onboarding = $request->session()->get('onboarding', []);

        if ($request->hasFile('manual_payment_qr')) {
            $qrPath = $request->file('manual_payment_qr')->store('qr', 'public');
            $data['manual_payment_qr_path'] = $qrPath;
            try {
                @copy(storage_path('app/public/' . $qrPath), public_path('storage/' . $qrPath));
            } catch (\Throwable $e) {}
        } elseif (isset($onboarding['step4']['manual_payment_qr_path'])) {
            $data['manual_payment_qr_path'] = $onboarding['step4']['manual_payment_qr_path'];
        }

        $onboarding['step4'] = $data;
        $request->session()->put('onboarding', $onboarding);

        return redirect()->route('owner.onboard.step5');
    }

    public function showStep5(Request $request)
    {
        $onboarding = $request->session()->get('onboarding', []);
        if (empty($onboarding['step1']) || empty($onboarding['step2']) || empty($onboarding['step3'])) {
            return redirect()->route('owner.onboard.step1')
                ->with('error', 'Please complete all previous steps before reviewing.');
        }

        $platformSettings = PlatformSetting::get();
        return view('owner.onboard.step5', compact('onboarding', 'platformSettings'));
    }

    public function submit(Request $request)
    {
        $request->validate([
            'agree_terms' => 'accepted',
        ]);

        $onboarding = $request->session()->get('onboarding', []);
        if (empty($onboarding['step1']) || empty($onboarding['step2']) || empty($onboarding['step3'])) {
            return redirect()->route('owner.onboard.step1')
                ->with('error', 'Incomplete onboarding session. Please review each step.');
        }

        $user = Auth::user();
        $s1 = $onboarding['step1'];
        $s2 = $onboarding['step2'];
        $s3 = $onboarding['step3'];
        $s4 = $onboarding['step4'] ?? [];

        $venue = DB::transaction(function () use ($user, $s1, $s2, $s3, $s4) {
            // 1. Create Venue
            $baseSlug = Str::slug($s1['name']);
            $slug = $baseSlug;
            $counter = 1;
            while (Venue::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . (++$counter);
            }

            $venue = Venue::create([
                'owner_id' => $user->id,
                'slug' => $slug,
                'name' => $s1['name'],
                'tagline' => $s1['tagline'] ?? null,
                'description' => $s1['description'] ?? null,
                'phone' => $s1['phone'],
                'email' => $s1['email'],
                'address' => $s1['address'],
                'city' => $s1['city'],
                'province' => $s1['province'],
                'country' => 'Philippines',
                'latitude' => $s2['latitude'],
                'longitude' => $s2['longitude'],
                'logo_path' => $s1['logo_path'] ?? null,
                'cover_image_path' => $s1['cover_image_path'] ?? null,
                'booking_ref_prefix' => $s1['booking_ref_prefix'],
                'status' => 'pending_approval',
                'is_active' => false,
                'is_verified' => false,
            ]);

            // 2. Create VenueSetting
            VenueSetting::create([
                'venue_id' => $venue->id,
                'venue_name' => $s1['name'],
                'tagline' => $s1['tagline'] ?? 'Premier Pickleball Facility',
                'description' => $s1['description'] ?? null,
                'phone' => $s1['phone'],
                'email' => $s1['email'],
                'address' => $s1['address'],
                'opening_time' => $s3['opening_time'] ?? '06:00',
                'closing_time' => $s3['closing_time'] ?? '00:00',
                'currency' => 'PHP',
                'currency_symbol' => '₱',
                'payment_mode' => $s4['payment_mode'] ?? 'manual_receipt',
                'manual_bank_name' => $s4['manual_bank_name'] ?? 'GCash / Maya',
                'manual_account_name' => $s4['manual_account_name'] ?? $s1['name'],
                'manual_account_number' => $s4['manual_account_number'] ?? $s1['phone'],
                'manual_payment_instructions' => $s4['manual_payment_instructions'] ?? "1. Transfer reservation amount to GCash/Maya.\n2. Note your reference code.\n3. Upload screenshot.",
                'manual_payment_qr' => $s4['manual_payment_qr_path'] ?? null,
                'holding_duration_seconds' => $s4['holding_duration_seconds'] ?? 120,
            ]);

            // 3. Create First Court
            $court = Court::create([
                'venue_id' => $venue->id,
                'name' => $s3['court_name'],
                'court_number' => 1,
                'type' => $s3['type'],
                'surface_type' => $s3['surface_type'],
                'price_per_hour' => (float) $s3['price_per_hour'],
                'max_players' => (int) $s3['max_players'],
                'opening_time' => $s3['opening_time'],
                'closing_time' => $s3['closing_time'],
                'image_path' => 'courts/court-1.jpg',
                'is_active' => true,
            ]);

            // 4. Create Free Trial Subscription (14 days default from platform settings)
            $trialDays = PlatformSetting::trialDays();
            CourtSubscription::create([
                'court_id' => $court->id,
                'owner_id' => $user->id,
                'venue_id' => $venue->id,
                'billing_cycle' => 'monthly',
                'price_snapshot' => PlatformSetting::subscriptionPriceMonthly(),
                'status' => 'trial',
                'trial_ends_at' => now()->addDays($trialDays),
                'starts_at' => now(),
                'ends_at' => now()->addDays($trialDays),
            ]);

            // 5. Update User's venue_id
            $user->update(['venue_id' => $venue->id]);

            return $venue;
        });

        $request->session()->forget('onboarding');

        return redirect()->route('owner.onboard.waiting')
            ->with('success', 'Venue application submitted! Our platform administrators are reviewing your submission.');
    }

    public function waiting(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $venue = $user->venue ?? $user->ownedVenues()->latest()->first();
        if (!$venue) {
            return redirect()->route('owner.onboard.step1');
        }

        if ($venue->status === 'approved' && $venue->is_active) {
            return redirect()->route('owner.dashboard')
                ->with('success', 'Your venue has been approved and is now live on the platform!');
        }

        return view('owner.onboard.waiting', compact('venue'));
    }
}
