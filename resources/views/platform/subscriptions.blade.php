@extends('layouts.app')

@section('content')
<div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/30 text-xs font-semibold text-amber-600 dark:text-amber-400 mb-2">
                <i class="fa-solid fa-coins"></i>
                <span>Platform Billing & Subscriptions</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-theme-heading tracking-tight">
                Subscription Plans & Court Billing
            </h1>
            <p class="text-xs text-theme-muted mt-1">
                Configure global platform pricing (₱300/court/month, ₱3,000/year), trial periods, and monitor active court subscriptions.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('platform.venues') }}" class="px-4 py-2.5 rounded-xl bg-stone-200 dark:bg-stone-800 hover:bg-stone-300 text-xs font-bold text-theme-heading border border-stone-300 dark:border-stone-700 flex items-center gap-2 transition-all">
                <i class="fa-solid fa-building-circle-check text-cyan-500"></i>
                <span>Manage Venues</span>
            </a>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="p-5 rounded-3xl glass-card border border-stone-200 dark:border-stone-800 shadow-lg">
            <div class="text-xs font-bold text-theme-muted">Total Courts</div>
            <div class="text-2xl font-black text-theme-heading mt-1">{{ $stats['total_courts'] }}</div>
            <div class="text-[11px] text-theme-muted mt-0.5">Across all venues</div>
        </div>
        <div class="p-5 rounded-3xl glass-card border border-stone-200 dark:border-stone-800 shadow-lg">
            <div class="text-xs font-bold text-emerald-600 dark:text-emerald-400">Active Paid Subs</div>
            <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ $stats['active_subs'] }}</div>
            <div class="text-[11px] text-theme-muted mt-0.5">Monthly & Annual plans</div>
        </div>
        <div class="p-5 rounded-3xl glass-card border border-stone-200 dark:border-stone-800 shadow-lg">
            <div class="text-xs font-bold text-cyan-600 dark:text-cyan-400">In 14-Day Trial</div>
            <div class="text-2xl font-black text-cyan-600 dark:text-cyan-400 mt-1">{{ $stats['trial_subs'] }}</div>
            <div class="text-[11px] text-theme-muted mt-0.5">Free trial courts</div>
        </div>
        <div class="p-5 rounded-3xl glass-card border border-stone-200 dark:border-stone-800 shadow-lg">
            <div class="text-xs font-bold text-rose-600 dark:text-rose-400">Expired / Grace</div>
            <div class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-1">{{ $stats['expired_subs'] }}</div>
            <div class="text-[11px] text-theme-muted mt-0.5">Requires renewal</div>
        </div>
    </div>

    <!-- Pricing Terms Card -->
    <div class="rounded-3xl glass-card border border-stone-200 dark:border-stone-800 p-6 sm:p-8 shadow-xl space-y-6">
        <div class="flex items-center justify-between pb-4 border-b border-stone-200 dark:border-stone-800">
            <div>
                <h3 class="text-base sm:text-lg font-black text-theme-heading">Global Platform Pricing Settings</h3>
                <p class="text-xs text-theme-muted mt-0.5">These rates apply to court owners creating subscriptions for their courts.</p>
            </div>
            <span class="px-3 py-1 rounded-xl bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 font-bold text-xs border border-cyan-500/20">
                Live Pricing
            </span>
        </div>

        <form action="{{ route('platform.subscriptions.settings') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            @csrf
            <div>
                <label class="block text-xs font-bold text-theme-heading mb-1.5">Monthly Price / Court (₱)</label>
                <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-theme-muted">₱</span>
                    <input type="number" step="10" name="monthly_price_per_court" value="{{ $settings->monthly_price_per_court }}" required
                           class="w-full pl-8 pr-3 py-2.5 rounded-xl bg-stone-100 dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-xs font-bold text-theme-heading focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-theme-heading mb-1.5">Annual Price / Court (₱)</label>
                <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-theme-muted">₱</span>
                    <input type="number" step="50" name="annual_price_per_court" value="{{ $settings->annual_price_per_court }}" required
                           class="w-full pl-8 pr-3 py-2.5 rounded-xl bg-stone-100 dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-xs font-bold text-theme-heading focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-theme-heading mb-1.5">Free Trial Period (Days)</label>
                <input type="number" name="trial_period_days" value="{{ $settings->trial_period_days }}" min="0" max="90" required
                       class="w-full px-3.5 py-2.5 rounded-xl bg-stone-100 dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-xs font-bold text-theme-heading focus:ring-2 focus:ring-cyan-500 focus:outline-none">
            </div>

            <div>
                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-black text-xs shadow-md transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Save Pricing Terms</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Active Court Subscriptions Ledger -->
    <div class="rounded-3xl glass-card border border-stone-200 dark:border-stone-800 overflow-hidden shadow-xl space-y-4 p-6">
        <h3 class="text-base font-black text-theme-heading">Court Subscriptions Registry</h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-stone-100/80 dark:bg-stone-900/80 border-b border-stone-200 dark:border-stone-800 text-[11px] font-bold text-theme-muted uppercase tracking-wider">
                    <tr>
                        <th class="py-3 px-4">Court & Venue</th>
                        <th class="py-3 px-4">Billing Plan</th>
                        <th class="py-3 px-4">Price / Term</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Expires</th>
                        <th class="py-3 px-4 text-right">Days Left</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200 dark:divide-stone-800">
                    @forelse($subscriptions as $sub)
                        <tr class="hover:bg-cyan-500/5 transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="font-black text-theme-heading">{{ $sub->court?->name ?? 'Court #' . $sub->court_id }}</div>
                                <div class="text-[11px] text-theme-muted">{{ $sub->venue?->name ?? 'Venue #' . $sub->venue_id }}</div>
                            </td>
                            <td class="py-3.5 px-4 uppercase font-bold text-theme-heading">
                                {{ $sub->plan }}
                            </td>
                            <td class="py-3.5 px-4 font-bold text-theme-heading">
                                ₱{{ number_format($sub->price, 2) }}
                            </td>
                            <td class="py-3.5 px-4">
                                @if($sub->status === 'trial')
                                    <span class="px-2.5 py-1 rounded-full bg-cyan-500/15 text-cyan-700 dark:text-cyan-400 font-bold text-[10px] uppercase border border-cyan-500/30">
                                        14-Day Trial
                                    </span>
                                @elseif($sub->status === 'active')
                                    <span class="px-2.5 py-1 rounded-full bg-emerald-500/15 text-emerald-700 dark:text-emerald-400 font-bold text-[10px] uppercase border border-emerald-500/30">
                                        Active
                                    </span>
                                @elseif($sub->status === 'expired')
                                    <span class="px-2.5 py-1 rounded-full bg-rose-500/15 text-rose-700 dark:text-rose-400 font-bold text-[10px] uppercase border border-rose-500/30">
                                        Expired
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full bg-stone-500/15 text-stone-600 font-bold text-[10px] uppercase">
                                        {{ $sub->status }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-theme-body font-medium">
                                {{ $sub->ends_at ? $sub->ends_at->format('M d, Y') : '—' }}
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                @php $days = $sub->daysUntilExpiry(); @endphp
                                @if($days > 3)
                                    <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ $days }} days</span>
                                @elseif($days > 0)
                                    <span class="font-bold text-amber-600 dark:text-amber-400 animate-pulse">{{ $days }} days</span>
                                @else
                                    <span class="font-bold text-rose-600 dark:text-rose-400">0 (Overdue)</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-theme-muted">
                                No court subscriptions found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($subscriptions->hasPages())
            <div class="p-2 border-t border-stone-200 dark:border-stone-800">
                {{ $subscriptions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
