<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Court;
use App\Models\FacilityPhoto;
use App\Models\Venue;
use App\Models\VenueSetting;
use App\Services\BookingService;
use App\Services\PayMongoService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VenueController extends Controller
{
    protected BookingService $bookingService;
    protected PayMongoService $paymongoService;

    public function __construct(BookingService $bookingService, PayMongoService $paymongoService)
    {
        $this->bookingService = $bookingService;
        $this->paymongoService = $paymongoService;
    }

    /**
     * Browse all active venues with proximity sorting & filters
     * GET /venues
     */
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));
        $city = trim($request->input('city', ''));
        $userLat = $request->input('lat');
        $userLng = $request->input('lng');

        $query = Venue::active()->with(['courts' => function ($q) {
            $q->where('is_active', true);
        }]);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if (!empty($city)) {
            $query->where('city', $city);
        }

        // Proximity calculation using SQL Haversine formula (km)
        if ($userLat !== null && $userLng !== null && is_numeric($userLat) && is_numeric($userLng)) {
            $lat = (float) $userLat;
            $lng = (float) $userLng;

            $haversine = "(6371 * acos(
                cos(radians(?)) * cos(radians(latitude))
                * cos(radians(longitude) - radians(?))
                + sin(radians(?)) * sin(radians(latitude))
            ))";

            $venues = $query
                ->selectRaw("venues.*, {$haversine} AS distance", [$lat, $lng, $lat])
                ->orderBy('distance')
                ->get();
        } else {
            $venues = $query->orderBy('name')->get();
        }

        $allCities = Venue::active()->distinct()->whereNotNull('city')->pluck('city')->sort()->values();

        return view('venues.index', compact('venues', 'search', 'city', 'allCities', 'userLat', 'userLng'));
    }

    /**
     * Individual venue page & dedicated booking engine
     * GET /venues/{slug}
     */
    public function show(string $slug, Request $request)
    {
        $venue = Venue::where('slug', $slug)
            ->with(['courts' => function ($q) {
                $q->where('is_active', true)->orderBy('court_number');
            }, 'photos' => function ($q) {
                $q->where('is_featured', true)->orderBy('sort_order');
            }])
            ->firstOrFail();

        $settings = VenueSetting::forVenue($venue->id);
        $courts = $venue->courts;
        $photos = $venue->photos;

        $selectedDate = $request->query('date', Carbon::today()->format('Y-m-d'));
        if (Carbon::parse($selectedDate)->lt(Carbon::today())) {
            $selectedDate = Carbon::today()->format('Y-m-d');
        }

        $availability = $this->bookingService->getAvailability($selectedDate, $venue->id);

        return view('venues.show', compact('venue', 'settings', 'courts', 'photos', 'selectedDate', 'availability'));
    }

    /**
     * JSON Proximity Search API
     * GET /api/venues/search?lat=X&lng=Y
     */
    public function search(Request $request)
    {
        $lat = $request->input('lat');
        $lng = $request->input('lng');
        $maxKm = (float) $request->input('max_km', 50);

        if ($lat === null || $lng === null || !is_numeric($lat) || !is_numeric($lng)) {
            $venues = Venue::active()->with('courts')->get();
            return response()->json([
                'success' => true,
                'count' => $venues->count(),
                'venues' => $venues,
            ]);
        }

        $lat = (float) $lat;
        $lng = (float) $lng;

        $venues = Venue::active()
            ->nearby($lat, $lng, $maxKm)
            ->with('courts')
            ->get();

        return response()->json([
            'success' => true,
            'origin' => ['latitude' => $lat, 'longitude' => $lng],
            'count' => $venues->count(),
            'venues' => $venues->map(function ($v) {
                return [
                    'id' => $v->id,
                    'slug' => $v->slug,
                    'name' => $v->name,
                    'city' => $v->city,
                    'province' => $v->province,
                    'distance_km' => round($v->distance, 1),
                    'price_range' => $v->getPriceRangeLabel(),
                    'courts_count' => $v->courts->count(),
                    'booking_url' => route('venues.show', $v->slug),
                    'latitude' => (float) $v->latitude,
                    'longitude' => (float) $v->longitude,
                ];
            }),
        ]);
    }
}
