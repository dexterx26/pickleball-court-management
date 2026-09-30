@extends('owner.onboard.layout', ['step' => 5])

@section('onboard-step')
<form action="{{ route('owner.onboard.submit') }}" method="POST" class="space-y-6">
    @csrf

    <div class="border-b border-stone-200 dark:border-stone-800 pb-4">
        <h2 class="text-xl font-black text-theme-heading flex items-center gap-2.5">
            <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-500 flex items-center justify-center text-sm font-black">5</span>
            Review Venue & Submit for Platform Approval
        </h2>
        <p class="text-xs text-theme-muted mt-1">Verify your information before submitting. Once submitted, platform administrators will approve your venue to go live.</p>
    </div>

    @php
        $s1 = $onboarding['step1'] ?? [];
        $s2 = $onboarding['step2'] ?? [];
        $s3 = $onboarding['step3'] ?? [];
        $s4 = $onboarding['step4'] ?? [];
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
        <!-- Venue Card -->
        <div class="p-4 rounded-2xl bg-stone-50 dark:bg-stone-900/60 border border-stone-200 dark:border-stone-800 space-y-2">
            <div class="flex items-center justify-between">
                <span class="font-bold text-theme-heading flex items-center gap-2">
                    <i class="fa-solid fa-building text-emerald-500"></i> Venue Identity
                </span>
                <a href="{{ route('owner.onboard.step1') }}" class="text-[11px] text-emerald-500 hover:underline font-bold">Edit</a>
            </div>
            <div class="font-extrabold text-sm text-theme-heading">{{ $s1['name'] ?? 'Venue' }}</div>
            <div class="text-theme-muted text-[11px]">{{ $s1['address'] ?? '' }}, {{ $s1['city'] ?? '' }}, {{ $s1['province'] ?? '' }}</div>
            <div class="text-[11px] text-theme-muted">Prefix: <span class="font-mono font-bold text-emerald-500">{{ $s1['booking_ref_prefix'] ?? 'PF' }}</span> • {{ $s1['phone'] ?? '' }}</div>
        </div>

        <!-- Location Card -->
        <div class="p-4 rounded-2xl bg-stone-50 dark:bg-stone-900/60 border border-stone-200 dark:border-stone-800 space-y-2">
            <div class="flex items-center justify-between">
                <span class="font-bold text-theme-heading flex items-center gap-2">
                    <i class="fa-solid fa-location-dot text-cyan-500"></i> Geocoordinates
                </span>
                <a href="{{ route('owner.onboard.step2') }}" class="text-[11px] text-cyan-500 hover:underline font-bold">Edit</a>
            </div>
            <div class="font-mono font-bold text-theme-heading text-sm">
                Lat: {{ $s2['latitude'] ?? '0' }}, Lng: {{ $s2['longitude'] ?? '0' }}
            </div>
            <div class="text-theme-muted text-[11px]">Enables proximity sorting on the public homepage. Nearby players will find your court first.</div>
        </div>

        <!-- Court Card -->
        <div class="p-4 rounded-2xl bg-stone-50 dark:bg-stone-900/60 border border-stone-200 dark:border-stone-800 space-y-2">
            <div class="flex items-center justify-between">
                <span class="font-bold text-theme-heading flex items-center gap-2">
                    <i class="fa-solid fa-table-tennis-paddle-ball text-purple-500"></i> Initial Court
                </span>
                <a href="{{ route('owner.onboard.step3') }}" class="text-[11px] text-purple-500 hover:underline font-bold">Edit</a>
            </div>
            <div class="font-extrabold text-sm text-theme-heading">{{ $s3['court_name'] ?? 'Court 1' }}</div>
            <div class="text-[11px] text-theme-muted capitalize">{{ $s3['type'] ?? 'indoor' }} • {{ $s3['surface_type'] ?? 'Acrylic' }} • Max {{ $s3['max_players'] ?? 4 }} Players</div>
            <div class="font-bold text-emerald-500">₱{{ number_format((float)($s3['price_per_hour'] ?? 150), 2) }} / hr <span class="text-theme-muted font-normal text-[11px]">({{ $s3['opening_time'] ?? '06:00' }} - {{ $s3['closing_time'] ?? '00:00' }})</span></div>
        </div>

        <!-- Payment Card -->
        <div class="p-4 rounded-2xl bg-stone-50 dark:bg-stone-900/60 border border-stone-200 dark:border-stone-800 space-y-2">
            <div class="flex items-center justify-between">
                <span class="font-bold text-theme-heading flex items-center gap-2">
                    <i class="fa-solid fa-credit-card text-amber-500"></i> Payment Collection
                </span>
                <a href="{{ route('owner.onboard.step4') }}" class="text-[11px] text-amber-500 hover:underline font-bold">Edit</a>
            </div>
            <div class="font-bold text-sm text-theme-heading uppercase">{{ str_replace('_', ' ', $s4['payment_mode'] ?? 'manual_receipt') }}</div>
            <div class="text-[11px] text-theme-muted">
                Hold Duration: {{ $s4['holding_duration_seconds'] ?? 120 }}s • {{ $s4['manual_bank_name'] ?? 'GCash/Maya' }}
            </div>
        </div>
    </div>

    <!-- Trial & Terms Agreement Box -->
    <div class="p-5 rounded-2xl bg-gradient-to-r from-emerald-500/10 via-cyan-500/10 to-transparent border border-emerald-500/20 space-y-3">
        <div class="flex items-start gap-3">
            <div class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0 text-sm mt-0.5">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div class="text-xs space-y-1">
                <div class="font-black text-theme-heading text-sm">Platform Subscription & 14-Day Free Trial</div>
                <p class="text-theme-muted text-[11px] leading-relaxed">
                    Court owners enjoy a <strong>14-day free trial</strong> for each court added. Following the trial, court subscriptions are billed at <strong>₱{{ number_format($platformSettings->subscription_price_monthly, 0) }}/court/month</strong> (or ₱{{ number_format($platformSettings->subscription_price_annual, 0) }}/year with 2 months free). You can cancel or add courts anytime.
                </p>
            </div>
        </div>

        <label class="flex items-center gap-3 pt-2 border-t border-emerald-500/20 cursor-pointer text-xs">
            <input type="checkbox" name="agree_terms" value="1" required class="w-4 h-4 rounded text-emerald-500 focus:ring-emerald-500">
            <span class="font-bold text-theme-heading">I confirm the above details are accurate and agree to platform terms & subscription billing.</span>
        </label>
        @error('agree_terms') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
    </div>

    <div class="pt-6 border-t border-stone-200 dark:border-stone-800 flex items-center justify-between">
        <a href="{{ route('owner.onboard.step4') }}" class="px-5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 text-theme-muted font-bold text-xs hover:bg-stone-100 dark:hover:bg-stone-800 transition-colors flex items-center gap-2">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back</span>
        </a>
        <button type="submit" class="px-8 py-3.5 rounded-xl bg-gradient-to-r from-emerald-500 via-emerald-600 to-cyan-500 hover:from-emerald-400 hover:to-cyan-400 text-slate-950 font-black text-sm shadow-xl shadow-emerald-500/30 flex items-center gap-2 transition-transform hover:scale-[1.02]">
            <i class="fa-solid fa-paper-plane"></i>
            <span>Submit Venue for Approval</span>
        </button>
    </div>
</form>
@endsection
