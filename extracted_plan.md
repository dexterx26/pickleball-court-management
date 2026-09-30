Multi-Venue Pickleball Court

Reservation Platform

Implementation Plan & Architecture Guide

Based On

paddle-field-sports-center (single-tenant Laravel app)

Target

Full multi-tenant platform — multiple court owners per deployment

Stack

Laravel 11, Laravel Reverb, SQLite (dev), MySQL (prod), Vite

Date

2026-09-30

1. Executive Summary

The current app (paddle-field-sports-center) is a single-tenant pickleball court reservation system built for one court owner. This document describes the migration to a multi-tenant platform where:

Any court owner can self-register, create a venue, add courts, and configure payment independently.

Each venue is fully isolated: courts, bookings, payments, and settings belong to that venue only.

Players browse all active venues sorted by proximity (nearest first using geolocation).

All real-time features (Reverb WebSockets, slot holding, live calendar) remain fully functional per venue.

All existing features are preserved and scoped to each venue with minimal code restructuring.

2. Core Architecture: Shared Database + Venue Scoping

Chosen Approach: Single-Schema Multi-Tenancy

Every table gains a venue_id foreign key. All queries are scoped by the authenticated owner's venue. This preserves the current codebase with minimal refactoring — no separate databases, no separate schemas.

Why not separate databases per tenant?

Overkill for a pickleball app — low row counts per venue.

Dramatically simplifies deployment, backups, and schema migrations.

Reverb WebSocket channels can still be isolated per venue (courts.{venue_id}).

3. System Roles

Role

Description

platform_admin

NEW — Superadmin who approves venues and manages the entire platform

court_owner

Registers, creates a venue, adds courts, manages bookings and staff

admin_assistant

Staff hired by court owner — scoped module permissions, same venue

user

Registered player who can book courts at any venue

guest

Unauthenticated visitor browsing the platform

NOTE:  The old "admin" role is renamed to "platform_admin" to reflect its platform-wide scope.

4. Court Owner Journey (New User Flow)

Step 1:  Register as court_owner via email/password, Google, or Facebook.

Step 2:  Onboarding Wizard — Venue Info: name, address, city, province, logo upload.

Step 3:  Onboarding Wizard — Location: map pin or manual lat/lng (for proximity search).

Step 4:  Onboarding Wizard — Add First Court: name, type, surface, price/hr, hours.

Step 5:  Onboarding Wizard — Payment Setup: manual receipt / PayMongo / Xendit keys.

Step 6:  Review & Submit → venue status set to pending_approval.

Step 7:  Platform Admin reviews and approves the venue.

Step 8:  Venue goes live — visible on the public discovery page, sorted by proximity.

5. Database Schema

5.1  New Table: venues

venues  id                  BIGINT PK  owner_id            FK -> users.id           (the court_owner user)  slug                VARCHAR(100) UNIQUE      e.g. "paddle-field-nueva-ecija"  name                VARCHAR(150)             e.g. "Paddle Field Sports Center"  tagline             VARCHAR(255) NULLABLE  description         TEXT NULLABLE  phone               VARCHAR(30) NULLABLE  email               VARCHAR(100) NULLABLE  address             VARCHAR(500) NULLABLE  city                VARCHAR(100) NULLABLE  province            VARCHAR(100) NULLABLE  country             VARCHAR(100) DEFAULT "Philippines"  latitude            DECIMAL(10,7) NULLABLE   -- for proximity sorting  longitude           DECIMAL(10,7) NULLABLE   -- for proximity sorting  logo_path           VARCHAR(500) NULLABLE  cover_image_path    VARCHAR(500) NULLABLE  booking_ref_prefix  VARCHAR(10) DEFAULT "PF" -- e.g. "PF", "SM", "BG"  is_active           BOOLEAN DEFAULT false    -- set true by platform_admin  is_verified         BOOLEAN DEFAULT false    -- optional KYC badge  created_at, updated_at, deleted_at           -- soft deletes

5.2  Modified Tables (Add venue_id)

Table

Change

venue_settings

Add venue_id FK -> venues (UNIQUE) — one VenueSetting row per venue

courts

Add venue_id FK -> venues NOT NULL

bookings

Add venue_id FK -> venues NOT NULL — denormalized for fast ledger queries

booking_slots

Add venue_id FK -> venues NOT NULL — fast conflict detection

facility_photos

Add venue_id FK -> venues NOT NULL

users

Add venue_id FK -> venues NULLABLE — set for owners/assistants, NULL for players

5.3  New Migrations (9 files)

2026_10_01_000001_create_venues_table.php2026_10_01_000002_add_venue_id_to_venue_settings.php2026_10_01_000003_add_venue_id_to_courts_table.php2026_10_01_000004_add_venue_id_to_bookings_table.php2026_10_01_000005_add_venue_id_to_booking_slots_table.php2026_10_01_000006_add_venue_id_to_facility_photos_table.php2026_10_01_000007_add_venue_id_to_users_table.php2026_10_01_000008_add_platform_admin_role_to_users.php2026_10_01_000009_add_geocoordinates_to_venues_table.php

MIGRATION NOTE:  A backfill step creates one default venues row from existing venue_settings data, then sets venue_id = 1 on all existing rows so existing data is preserved.

6. URL Structure & Routing

6.1  Public Routes

GET  /                                   Platform homepage — venue discoveryGET  /venues                             Browse all active venuesGET  /venues/{slug}                      Individual venue page + booking engineGET  /venues/{slug}/track/{ref}          Track booking by reference numberGET  /api/venues/search                  Location-based search (JSON, lat/lng)GET  /api/venues/{slug}/availability     Court slots for this venuePOST /api/venues/{slug}/hold-slots       Hold timeslots at this venuePOST /api/venues/{slug}/cancel-hold/{ref}POST /api/venues/{slug}/manual-receiptPOST /api/xendit/webhook                 Global — routes by Xendit invoice IDPOST /api/paymongo/webhook               Global — routes by PayMongo checkout ID

6.2  Owner Portal Routes

GET  /owner/onboard             Onboarding wizard (new court owners)GET  /owner/dashboard           Scoped to owner's venueGET  /owner/courts              Courts at owner's venue onlyGET  /owner/bookings            This venue's reservations ledgerGET  /owner/settings            This venue's payment & config settingsGET  /owner/subscription        View all court subscriptions & billing historyPOST /owner/subscription/pay    Pay or renew a court subscription... all existing /owner/* routes remain, scoped by venue_id

6.3  Platform Admin Routes (New)

GET  /platform/dashboard                  Platform-wide statsGET  /platform/venues                     All venues — approve / reject / banGET  /platform/users                      All users across all venuesGET  /platform/subscriptions              All owner subscriptions & revenueGET  /platform/subscriptions/pricing      Edit per-court pricing & trial settingsPOST /platform/subscriptions/pricing      Save pricing config

7. Feature Preservation Map

Every existing feature is preserved — queries are scoped by venue_id only:

Current Feature

Multi-Tenant Change

Court availability calendar

WHERE venue_id = ? AND court_id = ?

Real-time slot holding (2-min)

Reverb channel courts.{venue_id}

Slot conflict detection

WHERE venue_id=? AND court_id=? AND date=?

PayMongo / Xendit payment

Keys stored in venue_settings per venue

Manual receipt upload & approval

Scoped to venue bookings

Per-court operating hours

courts.opening_time / closing_time — unchanged

Owner manual reservation

Scoped to owner's venue courts

Owner cancellation

Scoped to venue bookings

Admin assistant permissions

court_owner_id + venue_id on user

Social login (Google/Facebook)

Global auth — user books at any venue

Soft-deleted courts

Preserved, scoped to venue

Booking tracker pass

/venues/{slug}/track/{ref}

Facility gallery photos

facility_photos.venue_id

SQLite dev / MySQL prod switcher

Unchanged — same .env strategy

8. New Features

8.1  Proximity Sorting (Haversine Formula)

// VenueController@index — SQL Haversine distance calculation$venues = Venue::active()    ->selectRaw("*, (",        6371 * acos(            cos(radians(?)) * cos(radians(latitude))            * cos(radians(longitude) - radians(?))            + sin(radians(?)) * sin(radians(latitude))        )    ) AS distance", [$userLat, $userLng, $userLat])    ->orderBy('distance')    ->get();

8.2  Reverb WebSocket Channel Per Venue

// OLD — single global channelnew Channel('courts')// NEW — isolated per venuenew Channel('courts.' . $this->venueId)// Blade embeds venue ID — frontend subscribes to correct channelconst VENUE_ID = {{ $venue->id }};window.Echo.channel(`courts.${VENUE_ID}`).listen('.CourtSlotsUpdated', handler)

8.3  Booking Reference Format Per Venue

// Each venue configures its own 2-4 letter prefix during onboardingPF-20261001-XKQPR    (Paddle Field Sports Center)SM-20261001-ABCDE    (SM Pickleball Hub)BG-20261001-QRSTU    (Barangay Courts)

9. Models & Controllers

9.1  New: Venue Model

class Venue extends Model {    use SoftDeletes;    public function owner()    // belongsTo User (court_owner)    public function settings() // hasOne VenueSetting    public function courts()   // hasMany Court    public function bookings() // hasMany Booking    public function photos()   // hasMany FacilityPhoto    public function staff()    // hasMany User where venue_id = id    public function scopeActive($q)    public function scopeNearby($q, $lat, $lng, $km = 50)    public function getPriceRangeLabel(): string   // "P150 - P300 / hr"}

9.2  Updated VenueSetting — forVenue() replaces getSettings()

public static function forVenue(int $venueId): self{    return static::firstOrCreate(['venue_id' => $venueId], [        'opening_time'             => '06:00',        'closing_time'             => '00:00',        'payment_mode'             => 'manual_receipt',        'holding_duration_seconds' => 120,    ]);}

9.3  New Controllers

VenueController          GET /venues, GET /venues/{slug}OnboardingController     GET/POST /owner/onboard (5-step wizard)PlatformAdminController  GET/POST /platform/venues (approve/reject)

9.4  Updated BookingService — all methods gain $venueId

getAvailability(string $date, int $venueId): arrayholdSlots(int $courtId, string $date, array $slots, array $data, int $venueId): arraymanualReserveCourt(array $data, int $approvedBy, int $venueId): BookingcancelReservation(Booking $booking, ?string $reason, ?int $userId): Booking

10. Environment Configuration

Development — SQLite

APP_ENV=localDB_CONNECTION=sqliteDB_DATABASE=/path/to/database/database.sqliteREVERB_HOST=localhostREVERB_PORT=8080

Production — MySQL

APP_ENV=productionDB_CONNECTION=mysqlDB_HOST=your-mysql-hostDB_DATABASE=pickleball_platformDB_USERNAME=db_userDB_PASSWORD=secure_passwordREVERB_HOST=0.0.0.0REVERB_PORT=8080REVERB_SCHEME=https

11. Development Phases

Phase 1 — Database & Models   [1–2 days]

☐  Write 9 new migrations (venues table + venue_id columns on all tables)

☐  Run fresh migrate on SQLite dev environment

☐  Create Venue model with scopes and relationships

☐  Update VenueSetting, Court, Booking, BookingSlot, User, FacilityPhoto models

☐  Replace VenueSetting::getSettings() with forVenue($venueId)

☐  Update BookingService — all methods accept $venueId

☐  Write seeders: 1 platform_admin, 3 court_owners, 3 venues, 6-9 courts, 20 users

Phase 2 — Auth & Roles   [1 day]

☐  Add platform_admin role to model helpers and all middleware checks

☐  Write EnsureVenueOwnership middleware, register in bootstrap/app.php

☐  Update EnsureUserHasRole for new platform_admin role

☐  Update registration page — add "Register as Court Owner" option

Phase 3 — Onboarding Wizard   [1–2 days]

☐  Create 5-step onboarding blade views (resources/views/owner/onboard/)

☐  Build OnboardingController — persist each step in session, commit on final submit

☐  Auto-create VenueSetting with defaults when venue row is created

☐  Venue status = pending_approval after submit — owner sees waiting screen

Phase 4 — API & Booking Engine   [2–3 days]

☐  Scope all BookingApiController routes under /api/venues/{venue:slug}/

☐  Update Reverb broadcast channels to courts.{venueId}

☐  Refactor welcome.blade.php to venues/show.blade.php with VENUE_ID JS variable

☐  Update frontend Echo subscription to courts.${VENUE_ID}

☐  Update booking reference generator to use venue->booking_ref_prefix

☐  Webhook routes remain global — resolve venue via invoice/checkout ID lookup

Phase 5 — Public Frontend   [2–3 days]

☐  Platform homepage (/) — venue discovery with proximity sorting

☐  /venues browse page — cards + city/type filters

☐  /venues/{slug} — per-venue booking engine

☐  Geolocation prompt + fallback (city text search)

☐  Optional: Leaflet.js map showing all venues as pins

Phase 6 — Owner Portal Scoping   [1–2 days]

☐  Inject currentVenue via EnsureVenueOwnership middleware into all owner views

☐  Scope all OwnerController queries with venue_id = auth()->user()->venue_id

☐  Owner settings page loads VenueSetting::forVenue($venueId)

☐  Enforce at query level: owner cannot see bookings/courts from other venues

Phase 7 — Platform Admin Panel   [1–2 days]

☐  /platform/* routes + PlatformAdminController

☐  Venue list with approve/reject actions and reason modal

☐  Approval triggers notification email to court owner

☐  Platform-wide stats: total venues, courts, bookings, revenue

Phase 8 — Testing & Polish   [2–3 days]

☐  Update all existing feature tests to pass venue_id

☐  New: MultiVenueIsolationTest — owner cannot access another venue's data

☐  New: VenueOnboardingTest — full 5-step wizard flow end-to-end

☐  New: ProximitySortTest — venues sorted correctly by distance

☐  New: PlatformAdminTest — approve/reject flow end-to-end

☐  MySQL migration dry-run on production database clone

12. Timeline Estimate

Phase

Description

Duration

Phase 1

Database & Models

1–2 days

Phase 2

Auth & Roles

1 day

Phase 3

Onboarding Wizard

1–2 days

Phase 4

API & Booking Engine

2–3 days

Phase 5

Public Frontend

2–3 days

Phase 6

Owner Portal Scoping

1–2 days

Phase 7

Platform Admin Panel

1–2 days

Phase 8

Testing & Polish

2–3 days

TOTAL

~11–18 days

13. Existing Features — Zero Redesign Needed

The following are fully built and need only venue_id scoping:

[OK]  Real-time slot holding (2-min countdown, Reverb broadcast)

[OK]  Drag-and-click timeslot selection UI with live updates

[OK]  PayMongo checkout (GCash, Maya, Card)

[OK]  Xendit checkout integration

[OK]  Manual receipt upload + approval queue

[OK]  Per-court operating hours (opening_time / closing_time)

[OK]  Owner manual reservation / offline whole-court rental

[OK]  Owner cancellation of any user reservation

[OK]  Admin assistant with granular module permissions

[OK]  Social login (Google / Facebook OAuth via Laravel Socialite)

[OK]  Booking tracker pass (/track/{reference})

[OK]  Snapshot pricing (rate locked at time of booking)

[OK]  Court soft deletes (historical booking data preserved)

[OK]  Held timeslots counter per user

[OK]  Facility gallery photo management

[OK]  Dark mode UI with glassmorphism styling

[OK]  Mobile-responsive booking engine

[OK]  SweetAlert2 confirmation dialogs throughout

[OK]  SQLite dev / MySQL prod environment switcher (switch-env.bat)

14. User Interface Designs

The following mockups illustrate the visual design of the multi-venue platform. All screens use a dark navy color scheme (#0F1629) with green accents (#22C55E), glassmorphism cards, and premium typography. The existing single-venue UI style is preserved and extended for multi-venue discovery.

14.1  Platform Homepage — Venue Discovery  (/)

The public-facing homepage replaces the current single-venue landing page. Venues are displayed as cards sorted by the user's location (nearest first). A search bar and city filter allow manual location input as a fallback.

Screen 1: Platform Homepage — Venue Discovery

Figure: Public homepage showing venue cards sorted by distance, with search and map toggle

14.2  Venue Booking Engine  (/venues/{slug})

Each venue has its own booking page. The layout mirrors the existing single-venue booking engine, scoped to the selected venue's courts, operating hours, and pricing. The Reverb WebSocket channel connects to courts.{venueId} for real-time updates.

Screen 2: Venue Booking Engine

Figure: Per-venue booking page with court tabs, weekly date picker, hourly slot grid, and booking summary panel

14.3  Court Owner Onboarding Wizard  (/owner/onboard)

New court owners are guided through a 5-step wizard immediately after registration. Steps cover venue information, geolocation pin, court setup, payment configuration, and a review screen before submitting for platform admin approval.

Screen 3: Owner Onboarding Wizard (Step 1 — Venue Info)

Figure: 5-step onboarding wizard with progress stepper, form fields for venue details, and logo/cover image upload

14.4  Owner Dashboard  (/owner/dashboard)

The owner dashboard is scoped exclusively to the logged-in owner's venue. It displays today's booking stats, a real-time court schedule grid, a pending approvals panel with receipt thumbnails, and a recent bookings table.

Screen 4: Court Owner Dashboard

Figure: Owner portal dashboard with stats cards, today's schedule grid, pending approvals panel, and recent bookings table

14.5  Venue Settings — Payment Gateway  (/owner/settings)

Each venue independently configures its payment mode. The settings page supports Manual Receipt (GCash/Maya/bank with QR upload), PayMongo, and Xendit. Additional settings include slot hold duration, booking reference prefix, and currency.

Screen 5: Venue Settings — Payment Gateway Tab

Figure: Owner settings with payment mode selection (Manual Receipt / PayMongo / Xendit), QR upload, and hold timer slider

14.6  Platform Admin Panel  (/platform/venues)

The platform admin has a separate portal to manage all venues across the system. Admins can approve or reject venue applications, view booking volumes per venue, and manage platform-wide users and analytics.

Screen 6: Platform Admin — Venues Management

Figure: Platform admin panel showing all venues with Approved/Pending status badges and approve/reject/ban action buttons

15. Subscription Billing System

Court owners pay a subscription fee per court they add to the platform. An owner can manage multiple venues with multiple courts — each court requires an active subscription to remain publicly bookable. The per-court price is set by the platform admin and can be changed at any time.

15.1  Billing Model

Rule

Description

Billing unit

Per court — each court added by an owner requires one subscription

Billing cycles

Monthly or Annual (annual = 10 months price, 2 months free)

Price set by

Platform admin — editable at any time from /platform/subscriptions/pricing

Multiple venues

Owners can add unlimited venues; each court in every venue is billed separately

Trial period

Configurable free trial (default: 14 days) per court on first activation

Grace period

Configurable grace after expiry (default: 3 days) before court is suspended

Auto-suspend

If subscription expires and is not renewed, court is deactivated — no new bookings accepted

Existing bookings

Confirmed bookings at a suspended court are preserved and honoured

Payment methods

PayMongo or manual bank transfer — platform admin configures platform-level payment

Invoice/receipt

Auto-generated PDF invoice per payment, downloadable from owner billing page

15.2  New Database Tables

platform_settings (global platform config)

platform_settings  id                              BIGINT PK  subscription_price_monthly       DECIMAL(10,2) DEFAULT 300.00  subscription_price_annual        DECIMAL(10,2) DEFAULT 3000.00  trial_days                       INT DEFAULT 14  grace_period_days               INT DEFAULT 3  auto_suspend_on_expiry          BOOLEAN DEFAULT true  platform_payment_mode           ENUM("paymongo","manual") DEFAULT "paymongo"  platform_paymongo_secret_key     VARCHAR(200) NULLABLE  platform_paymongo_public_key     VARCHAR(200) NULLABLE  platform_bank_name              VARCHAR(150) NULLABLE  platform_bank_account_name      VARCHAR(150) NULLABLE  platform_bank_account_number    VARCHAR(100) NULLABLE  created_at, updated_at

court_subscriptions (one row per court per billing period)

court_subscriptions  id                  BIGINT PK  court_id            FK -> courts.id  owner_id            FK -> users.id   (court_owner)  venue_id            FK -> venues.id  billing_cycle       ENUM("monthly", "annual") DEFAULT "monthly"  price_snapshot      DECIMAL(10,2)  -- price locked at time of payment  status              ENUM("trial", "active", "expired", "suspended", "cancelled")  trial_ends_at       TIMESTAMP NULLABLE  starts_at           TIMESTAMP  ends_at             TIMESTAMP      -- next renewal date  cancelled_at        TIMESTAMP NULLABLE  created_at, updated_at

subscription_invoices (payment record per renewal)

subscription_invoices  id                      BIGINT PK  subscription_id         FK -> court_subscriptions.id  owner_id                FK -> users.id  court_id                FK -> courts.id  invoice_number          VARCHAR(50) UNIQUE   e.g. "INV-2026-001"  amount                  DECIMAL(10,2)  billing_cycle           ENUM("monthly", "annual")  status                  ENUM("unpaid", "paid", "refunded")  payment_method          VARCHAR(50) NULLABLE  paymongo_checkout_id    VARCHAR(200) NULLABLE  paid_at                 TIMESTAMP NULLABLE  period_start            DATE  period_end              DATE  receipt_path            VARCHAR(500) NULLABLE  created_at, updated_at

MIGRATIONS:  New migrations needed: 2026_10_01_000010_create_platform_settings_table.php, 2026_10_01_000011_create_court_subscriptions_table.php, 2026_10_01_000012_create_subscription_invoices_table.php

15.3  Court Status Enforcement Logic

A court's bookable status is determined by its subscription state:

// Court model — isBookable() checkpublic function isBookable(): bool{    $sub = $this->activeSubscription;    if (!$sub) return false;    // Trial still valid    if ($sub->status === "trial" && now()->lt($sub->trial_ends_at)) return true;    // Active subscription    if ($sub->status === "active" && now()->lt($sub->ends_at)) return true;    // Within grace period after expiry    $grace = $sub->ends_at->addDays(platform_setting("grace_period_days", 3));    if ($sub->status === "expired" && now()->lt($grace)) return true;    return false;  // suspended / cancelled / expired past grace}// BookingApiController — rejects hold requests for non-bookable courtsif (!$court->isBookable()) {    return response()->json([        "error" => "This court is currently unavailable for booking.",        "reason" => "subscription_expired"    ], 403);}

15.4  Scheduled Jobs (Laravel Scheduler)

Job

Schedule

Action

CheckSubscriptionExpiry

Daily 00:01

Mark expired subscriptions, send renewal reminder emails

SuspendExpiredCourts

Daily 00:05

Set court status to suspended if past grace period

GenerateRenewalInvoices

Daily 08:00

Create upcoming invoices 7 days before renewal date

SendRenewalReminders

Daily 08:00

Email owners 7 days and 1 day before subscription expires

SendOverdueNotices

Daily 09:00

Email owners when court is suspended due to non-payment

15.5  New Models

CourtSubscription  scopeActive($q)       -- status active or trial, not expired  scopeExpired($q)      -- past ends_at  isInTrial(): bool  isActive(): bool  isInGracePeriod(): bool  daysUntilExpiry(): intSubscriptionInvoice  getInvoiceNumberAttribute()   -- formatted e.g. INV-2026-001  getReceiptUrlAttribute()      -- storage URL  markAsPaid(string $method)    -- updates status + paid_atPlatformSetting (singleton)  static get(): self            -- firstOrCreate with defaults  static subscriptionPriceMonthly(): float  static subscriptionPriceAnnual(): float  static trialDays(): int  static gracePeriodDays(): int

15.6  New Controllers

OwnerSubscriptionController  index()       GET  /owner/subscription       -- list all court subscriptions  pay()         POST /owner/subscription/pay   -- initiate payment for a court  history()     GET  /owner/subscription/history -- all invoicesPlatformSubscriptionController  index()       GET  /platform/subscriptions          -- all owners, revenue stats  pricing()     GET  /platform/subscriptions/pricing  -- edit price form  savePricing() POST /platform/subscriptions/pricing  -- save price + trial/grace

15.7  Owner Multi-Venue + Multi-Court Billing Example

Owner

Venue

Court

Status

Monthly Cost

Allan

Paddle Field Sports Center

Court 1

Active

P 300

Allan

Paddle Field Sports Center

Court 2

Active

P 300

Allan

Paddle Field Sports Center

Court 3

Trial

P 0 (14-day trial)

Allan

SM Pickleball Hub

Court 1

Active

P 300

Allan

SM Pickleball Hub

Court 2

Expired

P 300 (past due)

TOTAL (active)

P 1,200 / mo

PRICING NOTE:  The per-court price (P300 in this example) is set once by the platform admin and automatically applies to all new subscriptions. Existing active subscriptions are not retroactively changed — the price_snapshot column preserves the rate paid.

15.8  Subscription UI Screens

Screen 7: Owner Subscription Page  (/owner/subscription)

Court owners see all their courts across all venues in a single billing view. Each court shows its subscription status (Active, Trial, Expired), billing cycle, price, and expiry date. Expired courts show a highlighted Pay Now button. A billing history table below shows all past invoices with download links.

Screen 7: Owner Subscription & Billing Page

Figure: Owner billing page showing per-court subscription status across multiple venues, account summary, and billing history

Screen 8: Platform Admin — Subscription Pricing  (/platform/subscriptions/pricing)

The platform admin configures the global per-court subscription price. Monthly and annual prices are editable inline. Trial period, grace period, and auto-suspend behaviour are all configurable from this single screen. A live table below shows all active owner subscriptions and their billing status.

Screen 8: Platform Admin — Subscription Pricing Configuration

Figure: Admin panel for editing per-court monthly/annual price, trial days, grace period, and viewing all active subscriptions