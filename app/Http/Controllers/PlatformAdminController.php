<?php

namespace App\Http\Controllers;

use App\Models\Court;
use App\Models\CourtSubscription;
use App\Models\PlatformSetting;
use App\Models\Venue;
use Illuminate\Http\Request;

class PlatformAdminController extends Controller
{
    /**
     * Venues Management Ledger & Approval Queue
     * GET /platform/venues
     */
    public function venues(Request $request)
    {
        $status = $request->input('status', 'all');
        $search = trim($request->input('search', ''));

        $query = Venue::with(['owner', 'courts'])->withCount(['courts', 'bookings']);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('prefix', 'like', "%{$search}%")
                  ->orWhereHas('owner', function ($oq) use ($search) {
                      $oq->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $venues = $query->orderByDesc('id')->paginate(15)->withQueryString();

        $counts = [
            'all' => Venue::count(),
            'pending' => Venue::where('status', 'pending')->count(),
            'approved' => Venue::where('status', 'approved')->count(),
            'rejected' => Venue::where('status', 'rejected')->count(),
        ];

        return view('platform.venues', compact('venues', 'status', 'search', 'counts'));
    }

    /**
     * Approve pending venue
     * POST /platform/venues/{id}/approve
     */
    public function approveVenue(int $id)
    {
        $venue = Venue::findOrFail($id);
        $venue->update([
            'status' => 'approved',
            'is_active' => true,
            'is_verified' => true,
            'rejection_reason' => null,
        ]);

        return back()->with('success', "Venue '{$venue->name}' has been approved and is now live on the public discovery network!");
    }

    /**
     * Reject pending venue
     * POST /platform/venues/{id}/reject
     */
    public function rejectVenue(Request $request, int $id)
    {
        $venue = Venue::findOrFail($id);
        $reason = $request->input('reason', 'Application did not meet platform guidelines.');

        $venue->update([
            'status' => 'rejected',
            'is_active' => false,
            'rejection_reason' => $reason,
        ]);

        return back()->with('info', "Venue '{$venue->name}' has been rejected.");
    }

    /**
     * Toggle Venue active status
     * POST /platform/venues/{id}/toggle-status
     */
    public function toggleVenueStatus(int $id)
    {
        $venue = Venue::findOrFail($id);
        $venue->update(['is_active' => !$venue->is_active]);

        $statusText = $venue->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Venue '{$venue->name}' has been {$statusText}.");
    }

    /**
     * Platform Subscriptions & Global Pricing
     * GET /platform/subscriptions
     */
    public function subscriptions(Request $request)
    {
        $settings = PlatformSetting::getSettings();

        $subscriptions = CourtSubscription::with(['venue', 'court'])
            ->orderByDesc('id')
            ->paginate(20);

        $stats = [
            'total_courts' => Court::count(),
            'active_subs' => CourtSubscription::where('status', 'active')->count(),
            'trial_subs' => CourtSubscription::where('status', 'trial')->count(),
            'expired_subs' => CourtSubscription::where('status', 'expired')->count(),
        ];

        return view('platform.subscriptions', compact('settings', 'subscriptions', 'stats'));
    }

    /**
     * Update Platform Pricing Settings
     * POST /platform/subscriptions/settings
     */
    public function updateSubscriptionSettings(Request $request)
    {
        $data = $request->validate([
            'monthly_price_per_court' => 'required|numeric|min:0',
            'annual_price_per_court' => 'required|numeric|min:0',
            'trial_period_days' => 'required|integer|min:0|max:90',
            'grace_period_days' => 'required|integer|min:0|max:30',
        ]);

        $settings = PlatformSetting::getSettings();
        $settings->update($data);

        return back()->with('success', 'Platform subscription pricing and trial terms updated successfully!');
    }
}
