@extends('layouts.app')

@section('content')
<div class="min-h-screen py-10 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto">
    <!-- Header -->
    <div class="text-center mb-8 space-y-2">
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-bold uppercase tracking-wider">
            <i class="fa-solid fa-sparkles"></i> Court Owner Portal Setup
        </div>
        <h1 class="text-3xl sm:text-4xl font-black text-theme-heading tracking-tight">
            Register Your Pickleball Venue
        </h1>
        <p class="text-sm text-theme-muted max-w-xl mx-auto">
            Complete the 5-step setup to launch your sports facility, configure courts, and start accepting automated bookings.
        </p>
    </div>

    <!-- Stepper Navigation -->
    @php
        $currentStep = $step ?? 1;
        $steps = [
            1 => ['title' => 'Venue Info', 'icon' => 'fa-building'],
            2 => ['title' => 'Location Pin', 'icon' => 'fa-location-dot'],
            3 => ['title' => 'First Court', 'icon' => 'fa-table-tennis-paddle-ball'],
            4 => ['title' => 'Payment Mode', 'icon' => 'fa-credit-card'],
            5 => ['title' => 'Review & Launch', 'icon' => 'fa-check-circle'],
        ];
    @endphp

    <div class="mb-10">
        <div class="grid grid-cols-5 gap-2 sm:gap-4 relative">
            @foreach($steps as $sNum => $sInfo)
                @php
                    $isCompleted = $sNum < $currentStep;
                    $isActive = $sNum === $currentStep;
                @endphp
                <div class="flex flex-col items-center text-center">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl flex items-center justify-center font-black text-sm sm:text-base transition-all shadow-lg
                        {{ $isActive ? 'bg-gradient-to-tr from-emerald-500 to-cyan-500 text-slate-950 shadow-emerald-500/30 scale-105 ring-4 ring-emerald-500/20' : '' }}
                        {{ $isCompleted ? 'bg-emerald-500 text-slate-950 shadow-emerald-500/20' : '' }}
                        {{ !$isActive && !$isCompleted ? 'bg-stone-200 dark:bg-stone-800 text-theme-muted border border-stone-300 dark:border-stone-700' : '' }}">
                        @if($isCompleted)
                            <i class="fa-solid fa-check"></i>
                        @else
                            <i class="fa-solid {{ $sInfo['icon'] }}"></i>
                        @endif
                    </div>
                    <div class="mt-2 text-[11px] sm:text-xs font-bold {{ $isActive ? 'text-emerald-500 dark:text-emerald-400' : ($isCompleted ? 'text-theme-heading' : 'text-theme-muted') }} hidden sm:block">
                        Step {{ $sNum }}
                    </div>
                    <div class="text-[10px] sm:text-[11px] font-medium text-theme-muted truncate max-w-[80px] sm:max-w-none">
                        {{ $sInfo['title'] }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Step Card Container -->
    <div class="glass-panel rounded-3xl p-6 sm:p-10 shadow-2xl border border-stone-200 dark:border-stone-800/80">
        @if(session('error'))
            <div class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-500 text-xs font-semibold flex items-center gap-3">
                <i class="fa-solid fa-circle-exclamation text-base"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @yield('onboard-step')
    </div>
</div>
@endsection
