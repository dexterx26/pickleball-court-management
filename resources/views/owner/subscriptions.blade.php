@extends('layouts.owner')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-stone-200 dark:border-stone-800 gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-500/10 border border-cyan-500/30 text-xs font-semibold text-cyan-600 dark:text-cyan-400 mb-2">
                <i class="fa-solid fa-credit-card"></i>
                <span>Court-Level Billing</span>
            </div>
            <h2 class="text-xl sm:text-2xl font-black text-theme-heading tracking-tight">Court Subscriptions & Licensing</h2>
            <p class="text-xs text-theme-muted">
                Each court operates on a flexible subscription (₱{{ number_format($platformSettings->monthly_price_per_court, 0) }}/month or ₱{{ number_format($platformSettings->annual_price_per_court, 0) }}/year with 14-day free trial).
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3.5 py-1.5 rounded-xl bg-stone-100 dark:bg-stone-900 border border-stone-200 dark:border-stone-800 text-xs font-bold text-theme-heading">
                Venue: <span class="text-cyan-600 dark:text-cyan-400">{{ $venue->name }}</span>
            </span>
        </div>
    </div>

    <!-- Active Courts Billing Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($courts as $court)
            @php
                $activeSub = $court->activeSubscription;
                $isBookable = $court->isBookable();
                $daysRemaining = $activeSub ? $activeSub->daysUntilExpiry() : 0;
            @endphp
            <div class="rounded-3xl glass-panel border border-stone-200 dark:border-stone-800 p-6 shadow-xl flex flex-col justify-between space-y-5 group hover:border-cyan-500/40 transition-all">
                <div class="space-y-4">
                    <!-- Court Title & Status -->
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-md bg-stone-200 dark:bg-stone-800 text-theme-muted">
                                Court #{{ $court->court_number }}
                            </span>
                            <h3 class="text-lg font-black text-theme-heading mt-1">{{ $court->name }}</h3>
                            <p class="text-xs text-theme-muted">{{ $court->surface_type }} • {{ ucfirst($court->type) }}</p>
                        </div>

                        <div>
                            @if($activeSub && $activeSub->isInTrial())
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase bg-cyan-500/15 text-cyan-700 dark:text-cyan-400 border border-cyan-500/30">
                                    Free Trial
                                </span>
                            @elseif($activeSub && $activeSub->status === 'active')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase bg-emerald-500/15 text-emerald-700 dark:text-emerald-400 border border-emerald-500/30">
                                    Active
                                </span>
                            @elseif($activeSub && $activeSub->isInGracePeriod())
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase bg-amber-500/15 text-amber-700 dark:text-amber-400 border border-amber-500/30 animate-pulse">
                                    Grace Period
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase bg-rose-500/15 text-rose-700 dark:text-rose-400 border border-rose-500/30">
                                    Expired
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Subscription Details -->
                    <div class="p-4 rounded-2xl bg-stone-100 dark:bg-stone-900 border border-stone-200 dark:border-stone-800 space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-theme-muted">Current Plan:</span>
                            <span class="font-bold text-theme-heading uppercase">{{ $activeSub ? $activeSub->plan : 'None' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-theme-muted">Expires On:</span>
                            <span class="font-semibold text-theme-heading">{{ $activeSub && $activeSub->ends_at ? $activeSub->ends_at->format('M d, Y') : '—' }}</span>
                        </div>
                        <div class="flex items-center justify-between pt-1 border-t border-stone-200 dark:border-stone-800">
                            <span class="text-theme-muted">Days Left:</span>
                            @if($daysRemaining > 3)
                                <span class="font-black text-emerald-600 dark:text-emerald-400">{{ $daysRemaining }} Days</span>
                            @elseif($daysRemaining > 0)
                                <span class="font-black text-amber-600 dark:text-amber-400 animate-pulse">{{ $daysRemaining }} Days</span>
                            @else
                                <span class="font-black text-rose-600 dark:text-rose-400">0 (Overdue)</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Renewal Actions -->
                <div class="pt-2 space-y-2">
                    <form action="{{ route('owner.subscriptions.renew', $court->id) }}" method="POST" class="space-y-2">
                        @csrf
                        <div class="grid grid-cols-2 gap-2">
                            <button type="submit" name="plan" value="monthly"
                                    class="py-2.5 px-3 rounded-xl bg-stone-200 dark:bg-stone-800 hover:bg-stone-300 dark:hover:bg-stone-700 text-theme-heading font-bold text-xs border border-stone-300 dark:border-stone-700 transition-all flex flex-col items-center">
                                <span>Monthly</span>
                                <span class="text-[10px] text-cyan-600 dark:text-cyan-400 font-extrabold">₱{{ number_format($platformSettings->monthly_price_per_court, 0) }}/mo</span>
                            </button>
                            <button type="submit" name="plan" value="annual"
                                    class="py-2.5 px-3 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-black text-xs shadow-md shadow-amber-500/20 transition-all flex flex-col items-center">
                                <span>Annual (Save 17%)</span>
                                <span class="text-[10px] text-slate-900 font-extrabold">₱{{ number_format($platformSettings->annual_price_per_court, 0) }}/yr</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
