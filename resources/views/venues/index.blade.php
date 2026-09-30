@extends('layouts.app')

@section('content')
<section class="relative pt-10 pb-20 overflow-hidden">
    <!-- Ambient glowing gradients -->
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-80 bg-gradient-to-b from-cyan-500/10 via-amber-600/10 to-transparent blur-3xl pointer-events-none -z-10"></div>
    <div class="absolute top-32 right-12 w-72 h-72 bg-cyan-500/10 rounded-full blur-3xl pointer-events-none -z-10"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
        <!-- Hero Header -->
        <div class="text-center max-w-3xl mx-auto space-y-4">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-cyan-500/10 border border-cyan-500/30 text-xs font-semibold text-cyan-700 dark:text-cyan-400">
                <i class="fa-solid fa-location-dot animate-bounce"></i>
                <span>Multi-Venue Pickleball Network</span>
            </div>
            <h1 class="text-3xl sm:text-5xl font-black text-theme-heading tracking-tight leading-tight">
                Find & Book Courts <br>
                <span class="bg-gradient-to-r from-amber-600 via-cyan-600 to-cyan-500 bg-clip-text text-transparent">
                    Near Your Location
                </span>
            </h1>
            <p class="text-sm sm:text-base text-theme-body max-w-xl mx-auto">
                Explore premier tournament-certified pickleball facilities across the country. Check live availability, reserve courts, and play instantly.
            </p>
        </div>

        <!-- Filter & Search Bar -->
        <div class="glass-card p-4 sm:p-6 rounded-3xl shadow-xl border border-stone-200 dark:border-stone-800">
            <form action="{{ route('venues.index') }}" method="GET" id="search-form" class="space-y-4">
                <input type="hidden" name="lat" id="form-lat" value="{{ $userLat }}">
                <input type="hidden" name="lng" id="form-lng" value="{{ $userLng }}">

                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
                    <!-- Search Input -->
                    <div class="md:col-span-6 relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-theme-muted"></i>
                        <input type="text" name="search" value="{{ $search }}"
                               placeholder="Search venue name, address or landmark..."
                               class="w-full pl-11 pr-4 py-3 rounded-2xl bg-stone-100 dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-sm font-semibold text-theme-heading focus:ring-2 focus:ring-cyan-500 focus:outline-none transition-all">
                    </div>

                    <!-- City Dropdown -->
                    <div class="md:col-span-3">
                        <select name="city" onchange="document.getElementById('search-form').submit()"
                                class="w-full px-4 py-3 rounded-2xl bg-stone-100 dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-sm font-semibold text-theme-heading focus:ring-2 focus:ring-cyan-500 focus:outline-none transition-all">
                            <option value="">All Cities / Regions</option>
                            @foreach($allCities as $c)
                                <option value="{{ $c }}" {{ $city === $c ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Action Buttons -->
                    <div class="md:col-span-3 flex gap-2">
                        <button type="submit" class="flex-1 py-3 px-4 rounded-2xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-sm shadow-lg shadow-cyan-500/20 transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-filter"></i>
                            <span>Search</span>
                        </button>
                        <button type="button" onclick="detectUserLocation()" id="btn-detect-gps"
                                title="Sort by closest to me"
                                class="px-4 py-3 rounded-2xl {{ $userLat ? 'bg-amber-500 text-slate-950' : 'bg-stone-200 dark:bg-stone-800 text-theme-heading hover:bg-stone-300' }} font-bold text-sm border border-stone-300 dark:border-stone-700 transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-crosshairs"></i>
                            <span class="hidden lg:inline">{{ $userLat ? 'GPS Active' : 'Near Me' }}</span>
                        </button>
                    </div>
                </div>

                <!-- Active Location Notification -->
                @if($userLat && $userLng)
                    <div class="flex items-center justify-between text-xs bg-cyan-500/10 text-cyan-800 dark:text-cyan-300 px-4 py-2 rounded-xl border border-cyan-500/30">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-location-crosshairs text-cyan-500"></i>
                            <span>Sorted by distance from your coordinates ({{ round($userLat, 4) }}, {{ round($userLng, 4) }})</span>
                        </div>
                        <a href="{{ route('venues.index', array_filter(['search' => $search, 'city' => $city])) }}" class="text-xs font-bold underline hover:text-cyan-600">Clear GPS</a>
                    </div>
                @endif
            </form>
        </div>

        <!-- Venue Cards Grid -->
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-theme-heading flex items-center gap-2">
                    <i class="fa-solid fa-building-circle-check text-cyan-600"></i>
                    <span>Available Venues</span>
                    <span class="text-xs px-2.5 py-0.5 rounded-full bg-stone-200 dark:bg-stone-800 text-theme-muted font-bold">
                        {{ $venues->count() }}
                    </span>
                </h2>
                <div class="text-xs text-theme-muted">
                    Instant confirmation & live court sync
                </div>
            </div>

            @if($venues->isEmpty())
                <div class="text-center py-16 px-4 rounded-3xl glass-card border border-stone-200 dark:border-stone-800 space-y-4">
                    <div class="w-16 h-16 rounded-2xl bg-amber-500/10 text-amber-600 flex items-center justify-center mx-auto text-2xl">
                        <i class="fa-solid fa-location-dot"></i>
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-lg font-bold text-theme-heading">No venues found</h3>
                        <p class="text-xs text-theme-muted max-w-sm mx-auto">We couldn't find any approved venues matching your filter criteria. Try clearing your search or city selection.</p>
                    </div>
                    <a href="{{ route('venues.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-stone-200 dark:bg-stone-800 text-theme-heading text-xs font-bold hover:bg-stone-300">
                        <i class="fa-solid fa-rotate-left"></i>
                        <span>Reset Filters</span>
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($venues as $venue)
                        <div class="group rounded-3xl glass-card border border-stone-200 dark:border-stone-800 overflow-hidden hover:shadow-2xl hover:border-cyan-500/50 transition-all flex flex-col">
                            <!-- Card Header Image / Cover -->
                            <div class="relative h-48 w-full bg-slate-900 overflow-hidden">
                                @if($venue->cover_image_url)
                                    <img src="{{ $venue->cover_image_url }}" alt="{{ $venue->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-slate-900 via-cyan-950 to-stone-900 flex items-center justify-center">
                                        <i class="fa-solid fa-table-tennis-paddle-ball text-4xl text-cyan-500/30"></i>
                                    </div>
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>

                                <!-- Badges over image -->
                                <div class="absolute top-3 left-3 flex flex-wrap gap-1.5">
                                    @if(isset($venue->distance))
                                        <span class="px-2.5 py-1 rounded-full bg-cyan-500 text-slate-950 font-black text-xs shadow-md flex items-center gap-1">
                                            <i class="fa-solid fa-route text-[10px]"></i>
                                            <span>{{ number_format($venue->distance, 1) }} km</span>
                                        </span>
                                    @endif
                                    <span class="px-2.5 py-1 rounded-full bg-black/60 backdrop-blur-md text-cascading-white font-bold text-xs border border-white/10">
                                        <i class="fa-solid fa-map-pin text-amber-400 mr-1"></i>{{ $venue->city ?: 'Philippines' }}
                                    </span>
                                </div>

                                <!-- Price badge -->
                                <div class="absolute bottom-3 right-3">
                                    <span class="px-3 py-1 rounded-xl bg-slate-900/90 backdrop-blur-md text-cyan-400 font-extrabold text-xs border border-cyan-500/30 shadow-lg">
                                        {{ $venue->getPriceRangeLabel() }}
                                    </span>
                                </div>
                            </div>

                            <!-- Card Body -->
                            <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                                <div>
                                    <div class="flex items-start justify-between gap-2">
                                        <h3 class="text-lg font-black text-theme-heading group-hover:text-cyan-600 dark:group-hover:text-cyan-400 transition-colors line-clamp-1">
                                            {{ $venue->name }}
                                        </h3>
                                        <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-md bg-stone-200 dark:bg-stone-800 text-theme-muted tracking-wider">
                                            {{ $venue->prefix }}
                                        </span>
                                    </div>

                                    <p class="text-xs text-theme-muted mt-1.5 line-clamp-2">
                                        {{ $venue->address ?: 'Complete tournament-ready indoor pickleball courts with pro amenities.' }}
                                    </p>

                                    <!-- Facility Specs -->
                                    <div class="grid grid-cols-2 gap-2 mt-4 pt-3 border-t border-stone-200/60 dark:border-stone-800/60 text-xs">
                                        <div class="flex items-center gap-2 text-theme-body">
                                            <div class="w-6 h-6 rounded-lg bg-cyan-500/10 text-cyan-600 flex items-center justify-center">
                                                <i class="fa-solid fa-border-all text-[11px]"></i>
                                            </div>
                                            <span class="font-semibold">{{ $venue->courts->count() }} {{ Str::plural('Court', $venue->courts->count()) }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 text-theme-body">
                                            <div class="w-6 h-6 rounded-lg bg-amber-500/10 text-amber-600 flex items-center justify-center">
                                                <i class="fa-solid fa-clock text-[11px]"></i>
                                            </div>
                                            <span class="font-semibold truncate">
                                                {{ $venue->settings ? \Carbon\Carbon::parse($venue->settings->opening_time)->format('g:i A') . ' - ' . \Carbon\Carbon::parse($venue->settings->closing_time)->format('g:i A') : 'Open Daily' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- CTA -->
                                <div class="pt-2">
                                    <a href="{{ route('venues.show', $venue->slug) }}"
                                       class="w-full py-3 px-4 rounded-2xl bg-gradient-to-r from-cyan-500 to-cyan-600 hover:from-cyan-400 hover:to-cyan-500 text-slate-950 font-black text-xs shadow-lg shadow-cyan-500/20 flex items-center justify-center gap-2 transition-all group-hover:scale-[1.02]">
                                        <span>Reserve Court</span>
                                        <i class="fa-solid fa-arrow-right text-[11px] group-hover:translate-x-1 transition-transform"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Venue Owner Callout Banner -->
        <div class="rounded-3xl p-8 sm:p-10 bg-gradient-to-r from-lead via-stone-900 to-stone-950 border border-stone-800 text-cascading-white relative overflow-hidden shadow-2xl">
            <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-cyan-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="max-w-2xl space-y-4 relative z-10">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-500/15 border border-cyan-500/30 text-xs font-semibold text-cyan-400">
                    <i class="fa-solid fa-bolt"></i>
                    <span>Court Owners & Sports Facilities</span>
                </div>
                <h3 class="text-2xl sm:text-3xl font-black tracking-tight">
                    Own a Pickleball Court? List Your Venue Today
                </h3>
                <p class="text-xs sm:text-sm text-stone-300 leading-relaxed">
                    Automate your schedule, eliminate double-bookings, accept GCash / PayMongo, and connect with thousands of active players in your city.
                    Start with our 14-day free trial — only ₱300 / court per month!
                </p>
                <div class="pt-2 flex flex-wrap gap-4 items-center">
                    <a href="{{ route('register') }}" class="px-6 py-3 rounded-2xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-black text-xs shadow-lg shadow-amber-500/20 flex items-center gap-2 transition-all hover:scale-105">
                        <i class="fa-solid fa-rocket"></i>
                        <span>Register As Court Owner</span>
                    </a>
                    <a href="{{ route('quick.login', 'owner') }}" class="px-5 py-3 rounded-2xl bg-stone-800 hover:bg-stone-700 text-cascading-white font-bold text-xs border border-stone-700 transition-all flex items-center gap-2">
                        <span>Try Owner Demo</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
    function detectUserLocation() {
        const btn = document.getElementById('btn-detect-gps');
        if (!navigator.geolocation) {
            Swal.fire({
                icon: 'warning',
                title: 'Geolocation Not Supported',
                text: 'Your browser does not support geolocation detection.',
                confirmButtonColor: '#06b6d4',
            });
            return;
        }

        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Finding...';
        btn.disabled = true;

        navigator.geolocation.getCurrentPosition(
            (pos) => {
                document.getElementById('form-lat').value = pos.coords.latitude;
                document.getElementById('form-lng').value = pos.coords.longitude;
                document.getElementById('search-form').submit();
            },
            (err) => {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
                Swal.fire({
                    icon: 'info',
                    title: 'Location Permission Denied',
                    text: 'Please allow location access to automatically sort venues by distance to you.',
                    confirmButtonColor: '#06b6d4',
                });
            },
            { timeout: 10000, enableHighAccuracy: true }
        );
    }
</script>
@endpush
@endsection
