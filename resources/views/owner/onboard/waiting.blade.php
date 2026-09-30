@extends('layouts.app')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-xl w-full text-center space-y-6">
        <!-- Status Icon -->
        <div class="relative w-24 h-24 mx-auto">
            <div class="w-24 h-24 rounded-3xl bg-amber-500/10 border-2 border-amber-500/30 text-amber-500 flex items-center justify-center text-4xl shadow-xl shadow-amber-500/10">
                <i class="fa-solid fa-hourglass-half animate-pulse"></i>
            </div>
            <div class="absolute -bottom-1 -right-1 w-8 h-8 rounded-full bg-emerald-500 text-slate-950 flex items-center justify-center text-sm font-black shadow-lg">
                <i class="fa-solid fa-clock"></i>
            </div>
        </div>

        <div class="space-y-2">
            <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs font-black uppercase tracking-wider">
                Application Status: Pending Approval
            </div>
            <h1 class="text-3xl font-black text-theme-heading tracking-tight">
                {{ $venue->name }} is Under Review
            </h1>
            <p class="text-sm text-theme-muted max-w-md mx-auto">
                Thank you for registering your pickleball facility! Our platform superadmin team is currently reviewing your venue submission.
            </p>
        </div>

        <!-- Venue Summary Card -->
        <div class="glass-panel rounded-3xl p-6 text-left space-y-4 border border-stone-200 dark:border-stone-800 text-xs">
            <div class="flex items-center justify-between pb-3 border-b border-stone-200 dark:border-stone-800">
                <div class="font-bold text-theme-heading flex items-center gap-2">
                    <i class="fa-solid fa-building text-emerald-500"></i> Venue Details
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-500/20 text-amber-400 border border-amber-500/30 uppercase">
                    Pending
                </span>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <span class="text-theme-muted text-[11px] block">Location</span>
                    <span class="font-semibold text-theme-heading">{{ $venue->city }}, {{ $venue->province }}</span>
                </div>
                <div>
                    <span class="text-theme-muted text-[11px] block">Booking Prefix</span>
                    <span class="font-mono font-bold text-emerald-500">{{ $venue->booking_ref_prefix }}</span>
                </div>
                <div>
                    <span class="text-theme-muted text-[11px] block">Contact</span>
                    <span class="font-semibold text-theme-heading">{{ $venue->phone }}</span>
                </div>
                <div>
                    <span class="text-theme-muted text-[11px] block">Submitted At</span>
                    <span class="font-semibold text-theme-heading">{{ $venue->created_at->format('M d, Y g:i A') }}</span>
                </div>
            </div>

            <div class="p-3 rounded-xl bg-stone-100 dark:bg-stone-900/60 border border-stone-200 dark:border-stone-800 flex items-center gap-3">
                <i class="fa-solid fa-circle-info text-cyan-500 text-base shrink-0"></i>
                <span class="text-[11px] text-theme-muted">
                    Upon approval, your venue will immediately become publicly bookable, and you'll receive full access to your Court Owner Dashboard.
                </span>
            </div>
        </div>

        <div class="flex items-center justify-center gap-3 pt-2">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="px-6 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 text-theme-muted font-bold text-xs hover:bg-stone-100 dark:hover:bg-stone-800 transition-colors cursor-pointer">
                    Sign Out
                </button>
            </form>
            <a href="{{ route('home') }}" class="px-6 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs shadow-md shadow-emerald-500/20 transition-all">
                Browse Public Venues
            </a>
        </div>
    </div>
</div>
@endsection
