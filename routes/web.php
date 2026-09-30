<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingApiController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\PlatformAdminController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\VenueController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Customer & Guest Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/venues', [VenueController::class, 'index'])->name('venues.index');
Route::get('/venues/{slug}', [VenueController::class, 'show'])->name('venues.show');
Route::get('/track/{reference}', [HomeController::class, 'trackBooking'])->name('booking.track');
Route::get('/lookup', [HomeController::class, 'lookup'])->name('booking.lookup');
Route::post('/booking/manual-receipt', [BookingApiController::class, 'submitManualReceipt'])->name('booking.manual_receipt');
Route::get('/xendit-checkout/{reference}', [BookingApiController::class, 'simulatedCheckout'])->name('xendit.simulated.checkout');
Route::get('/paymongo-checkout/{reference}', [BookingApiController::class, 'paymongoCheckout'])->name('paymongo.simulated.checkout');
Route::post('/simulate-payment/{reference}', [BookingApiController::class, 'simulatePayment'])->name('booking.simulate_payment');

/*
|--------------------------------------------------------------------------
| Public API Endpoints (Real-time slots, holds, status)
|--------------------------------------------------------------------------
*/
Route::prefix('api')->group(function () {
    Route::get('/venues/search', [VenueController::class, 'search'])->name('api.venues.search');
    Route::get('/venues/{slug}/availability', [BookingApiController::class, 'availability'])->name('api.venues.availability');
    Route::get('/availability', [BookingApiController::class, 'availability'])->name('api.availability');
    Route::post('/hold-slots', [BookingApiController::class, 'hold'])->name('api.hold');
    Route::get('/booking-status/{reference}', [BookingApiController::class, 'status'])->name('api.booking_status');
    Route::post('/cancel-hold/{reference}', [BookingApiController::class, 'cancelHold'])->name('api.cancel_hold');
    Route::post('/xendit/webhook', [BookingApiController::class, 'xenditWebhook'])->name('api.xendit.webhook');
    Route::post('/paymongo/webhook', [BookingApiController::class, 'paymongoWebhook'])->name('api.paymongo.webhook');
    Route::post('/paymongo/dynamic-qr/{reference}', [BookingApiController::class, 'generatePayMongoQr'])->name('api.paymongo.dynamic_qr');
});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/quick-login/{role}', [AuthController::class, 'quickLogin'])->name('quick.login');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Social Authentication (Google / Gmail & Facebook OAuth)
Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])->name('auth.social.redirect');
Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])->name('auth.social.callback');
Route::get('/auth/{provider}/mock', [SocialAuthController::class, 'showMock'])->name('auth.social.mock');
Route::post('/auth/{provider}/mock', [SocialAuthController::class, 'processMock'])->name('auth.social.mock.process');

/*
|--------------------------------------------------------------------------
| Protected Owner & Admin Portal
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:court_owner,admin,admin_assistant'])->prefix('owner')->name('owner.')->group(function () {
    // Court Owner Onboarding Wizard (Accessible before venue is created)
    Route::prefix('onboard')->name('onboard.')->group(function () {
        Route::get('/', [OnboardingController::class, 'index'])->name('index');
        Route::get('/step/1', [OnboardingController::class, 'showStep1'])->name('step1');
        Route::post('/step/1', [OnboardingController::class, 'postStep1'])->name('post_step1');
        Route::get('/step/2', [OnboardingController::class, 'showStep2'])->name('step2');
        Route::post('/step/2', [OnboardingController::class, 'postStep2'])->name('post_step2');
        Route::get('/step/3', [OnboardingController::class, 'showStep3'])->name('step3');
        Route::post('/step/3', [OnboardingController::class, 'postStep3'])->name('post_step3');
        Route::get('/step/4', [OnboardingController::class, 'showStep4'])->name('step4');
        Route::post('/step/4', [OnboardingController::class, 'postStep4'])->name('post_step4');
        Route::get('/step/5', [OnboardingController::class, 'showStep5'])->name('step5');
        Route::post('/submit', [OnboardingController::class, 'submit'])->name('submit');
        Route::get('/waiting', [OnboardingController::class, 'waiting'])->name('waiting');
    });

    // Overview & Schedule Dashboard (Scoped to venue via venue.owner middleware)
    Route::get('/', [OwnerController::class, 'dashboard'])->middleware(['venue.owner', 'module:schedule'])->name('dashboard');

    // Manual Receipt Approvals Queue
    Route::middleware('module:approvals')->group(function () {
        Route::get('/approvals', [OwnerController::class, 'approvals'])->name('approvals');
        Route::post('/approvals/{id}/approve', [OwnerController::class, 'approveBooking'])->name('approve');
        Route::post('/approvals/{id}/reject', [OwnerController::class, 'rejectBooking'])->name('reject');
    });

    // Courts Management (Pricing per hour, max players, photo, add/edit)
    Route::middleware('module:courts')->group(function () {
        Route::get('/courts', [OwnerController::class, 'courts'])->name('courts.index');
        Route::post('/courts', [OwnerController::class, 'storeCourt'])->name('courts.store');
        Route::put('/courts/{id}', [OwnerController::class, 'updateCourt'])->name('courts.update');
        Route::delete('/courts/{id}', [OwnerController::class, 'destroyCourt'])->name('courts.destroy');
        Route::post('/courts/{id}/restore', [OwnerController::class, 'restoreCourt'])->name('courts.restore');
    });

    // Facility Photos Management for Website Main Page
    Route::middleware('module:photos')->group(function () {
        Route::get('/photos', [OwnerController::class, 'photos'])->name('photos.index');
        Route::post('/photos', [OwnerController::class, 'storePhoto'])->name('photos.store');
        Route::put('/photos/{id}', [OwnerController::class, 'updatePhoto'])->name('photos.update');
        Route::delete('/photos/{id}', [OwnerController::class, 'destroyPhoto'])->name('photos.destroy');
    });

    // Venue & Payment Gateway Settings (Xendit vs Manual Receipt)
    Route::middleware('module:settings')->group(function () {
        Route::get('/settings', [OwnerController::class, 'settings'])->name('settings');
        Route::put('/settings', [OwnerController::class, 'updateSettings'])->name('settings.update');
    });

    // All Bookings Ledger (Schedule & Reservations)
    Route::middleware('module:schedule')->group(function () {
        Route::get('/bookings', [OwnerController::class, 'allBookings'])->name('bookings.index');
        Route::post('/bookings/reserve', [OwnerController::class, 'manualReserve'])->name('bookings.reserve');
        Route::post('/bookings/{id}/cancel', [OwnerController::class, 'cancelBooking'])->name('bookings.cancel');
    });

    // User Management (Accessible to Owner, Admin, and Admin Assistant with 'users' module access)
    Route::middleware('module:users')->prefix('users')->name('users.')->group(function () {
        Route::get('/', [OwnerController::class, 'usersIndex'])->name('index');
        Route::post('/', [OwnerController::class, 'storeUser'])->name('store');
        Route::put('/{id}', [OwnerController::class, 'updateUser'])->name('update');
        Route::delete('/{id}', [OwnerController::class, 'destroyUser'])->name('destroy');
        Route::post('/{id}/toggle-status', [OwnerController::class, 'toggleUserStatus'])->name('toggle');
        Route::get('/{id}/held-slots', [OwnerController::class, 'userHeldSlots'])->name('held_slots');
    });

    // Admin Assistants Management (Accessible only to Court Owners and System Administrators)
    Route::middleware('role:court_owner,admin')->prefix('assistants')->name('assistants.')->group(function () {
        Route::get('/', [OwnerController::class, 'assistantsIndex'])->name('index');
        Route::post('/', [OwnerController::class, 'storeAssistant'])->name('store');
        Route::put('/{id}', [OwnerController::class, 'updateAssistant'])->name('update');
        Route::delete('/{id}', [OwnerController::class, 'destroyAssistant'])->name('destroy');
        Route::post('/{id}/toggle-status', [OwnerController::class, 'toggleAssistantStatus'])->name('toggle');
    });

    // Court Subscriptions Management
    Route::get('/subscriptions', [OwnerController::class, 'subscriptions'])->name('subscriptions');
    Route::post('/subscriptions/{courtId}/renew', [OwnerController::class, 'renewSubscription'])->name('subscriptions.renew');
});

/*
|--------------------------------------------------------------------------
| Protected Platform Superadmin Portal
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:platform_admin,admin'])->prefix('platform')->name('platform.')->group(function () {
    Route::get('/venues', [PlatformAdminController::class, 'venues'])->name('venues');
    Route::post('/venues/{id}/approve', [PlatformAdminController::class, 'approveVenue'])->name('venues.approve');
    Route::post('/venues/{id}/reject', [PlatformAdminController::class, 'rejectVenue'])->name('venues.reject');
    Route::post('/venues/{id}/toggle-status', [PlatformAdminController::class, 'toggleVenueStatus'])->name('venues.toggle');

    Route::get('/subscriptions', [PlatformAdminController::class, 'subscriptions'])->name('subscriptions');
    Route::post('/subscriptions/settings', [PlatformAdminController::class, 'updateSubscriptionSettings'])->name('subscriptions.settings');
});

/*
|--------------------------------------------------------------------------
| Local Storage Asset Delivery (Fallback for Windows/XAMPP environments without symlinks)
|--------------------------------------------------------------------------
*/
Route::get('/storage/{path}', function (string $path) {
    if (str_contains($path, '..')) {
        abort(403);
    }

    $publicFile = public_path('storage/' . $path);
    if (file_exists($publicFile) && !is_dir($publicFile)) {
        return response()->file($publicFile, [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    $storageFile = storage_path('app/public/' . $path);
    if (file_exists($storageFile) && !is_dir($storageFile)) {
        return response()->file($storageFile, [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    abort(404);
})->where('path', '.*')->name('storage.serve');

