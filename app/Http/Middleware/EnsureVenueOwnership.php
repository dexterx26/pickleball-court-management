<?php

namespace App\Http\Middleware;

use App\Models\Venue;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVenueOwnership
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Platform Admin and System Admin have multi-venue management authority
        if ($user->isPlatformAdmin() || $user->isAdmin()) {
            $venue = null;
            if ($request->has('venue_id')) {
                $venue = Venue::find($request->input('venue_id'));
            }
            if (!$venue && $user->venue_id) {
                $venue = Venue::find($user->venue_id);
            }
            if (!$venue) {
                $venue = Venue::first();
            }

            $request->attributes->set('currentVenue', $venue);
            view()->share('currentVenue', $venue);

            return $next($request);
        }

        // Court Owner handling
        if ($user->isOwner()) {
            $venue = $user->venue ?? $user->ownedVenues()->latest()->first();

            // If newly registered court owner has no venue created yet
            if (!$venue) {
                if ($request->routeIs('owner.onboard.*') || $request->is('owner/onboard*')) {
                    return $next($request);
                }

                return redirect()->route('owner.onboard.step1')
                    ->with('info', 'Please complete the onboarding wizard to create and set up your venue.');
            }

            // Sync user's venue_id if not set
            if (!$user->venue_id && $venue) {
                $user->update(['venue_id' => $venue->id]);
            }

            // If venue is pending approval, allow waiting view or onboarding screens
            if ($venue->status === 'pending_approval' && !$request->routeIs('owner.onboard.*') && !$request->is('owner/onboard*')) {
                // If they are attempting to visit dashboard, we can let them see the pending banner
            }

            $request->attributes->set('currentVenue', $venue);
            view()->share('currentVenue', $venue);

            return $next($request);
        }

        // Admin Assistant handling
        if ($user->isAdminAssistant()) {
            $venue = $user->venue;
            if (!$venue && $user->court_owner_id) {
                $venue = Venue::where('owner_id', $user->court_owner_id)->first();
                if ($venue) {
                    $user->update(['venue_id' => $venue->id]);
                }
            }

            if (!$venue) {
                abort(403, 'Your staff account is not associated with an active venue.');
            }

            $request->attributes->set('currentVenue', $venue);
            view()->share('currentVenue', $venue);

            return $next($request);
        }

        return $next($request);
    }
}
