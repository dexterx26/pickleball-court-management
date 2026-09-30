@extends('owner.onboard.layout', ['step' => 3])

@section('onboard-step')
<form action="{{ route('owner.onboard.post_step3') }}" method="POST" class="space-y-6">
    @csrf

    <div class="border-b border-stone-200 dark:border-stone-800 pb-4">
        <h2 class="text-xl font-black text-theme-heading flex items-center gap-2.5">
            <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-500 flex items-center justify-center text-sm font-black">3</span>
            Set Up Your First Court
        </h2>
        <p class="text-xs text-theme-muted mt-1">Configure your primary court. You can add additional courts at any time from your owner dashboard.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 text-xs">
        <div class="sm:col-span-2">
            <label class="block font-bold text-theme-body mb-1">Court Name <span class="text-rose-500">*</span></label>
            <input type="text" name="court_name" value="{{ old('court_name', $onboarding['step3']['court_name'] ?? 'Court 1 - Championship Center') }}" required
                placeholder="e.g. Court 1 - Championship Center"
                class="w-full px-4 py-3 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading font-medium placeholder-stone-400 focus:outline-none focus:border-emerald-500">
            @error('court_name') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block font-bold text-theme-body mb-1">Court Type <span class="text-rose-500">*</span></label>
            <div class="grid grid-cols-2 gap-2">
                <label class="flex items-center justify-center gap-2 p-3 rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-900 cursor-pointer has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-500/5">
                    <input type="radio" name="type" value="indoor" {{ old('type', $onboarding['step3']['type'] ?? 'indoor') === 'indoor' ? 'checked' : '' }} class="sr-only">
                    <i class="fa-solid fa-house-chimney text-emerald-500"></i>
                    <span class="font-bold text-theme-heading">Indoor</span>
                </label>
                <label class="flex items-center justify-center gap-2 p-3 rounded-xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-900 cursor-pointer has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-500/5">
                    <input type="radio" name="type" value="outdoor" {{ old('type', $onboarding['step3']['type'] ?? '') === 'outdoor' ? 'checked' : '' }} class="sr-only">
                    <i class="fa-solid fa-sun text-amber-500"></i>
                    <span class="font-bold text-theme-heading">Outdoor</span>
                </label>
            </div>
            @error('type') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block font-bold text-theme-body mb-1">Surface Type <span class="text-rose-500">*</span></label>
            <input type="text" name="surface_type" value="{{ old('surface_type', $onboarding['step3']['surface_type'] ?? 'Pro Cushion Acrylic') }}" required
                placeholder="e.g. Pro Cushion Acrylic, Taraflex"
                class="w-full px-4 py-3 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading placeholder-stone-400 focus:outline-none focus:border-emerald-500">
            @error('surface_type') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block font-bold text-theme-body mb-1">Hourly Rate (₱ PHP) <span class="text-rose-500">*</span></label>
            <div class="relative">
                <span class="absolute left-4 top-3 text-stone-400 font-bold">₱</span>
                <input type="number" step="0.01" min="1" name="price_per_hour" value="{{ old('price_per_hour', $onboarding['step3']['price_per_hour'] ?? '150.00') }}" required
                    class="w-full pl-8 pr-4 py-3 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading font-black text-sm focus:outline-none focus:border-emerald-500">
            </div>
            @error('price_per_hour') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block font-bold text-theme-body mb-1">Max Players Capacity <span class="text-rose-500">*</span></label>
            <input type="number" min="2" max="10" name="max_players" value="{{ old('max_players', $onboarding['step3']['max_players'] ?? '4') }}" required
                class="w-full px-4 py-3 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading font-bold focus:outline-none focus:border-emerald-500">
            @error('max_players') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block font-bold text-theme-body mb-1">Daily Opening Time <span class="text-rose-500">*</span></label>
            <input type="time" name="opening_time" value="{{ old('opening_time', $onboarding['step3']['opening_time'] ?? '06:00') }}" required
                class="w-full px-4 py-3 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading font-mono focus:outline-none focus:border-emerald-500">
        </div>

        <div>
            <label class="block font-bold text-theme-body mb-1">Daily Closing Time <span class="text-rose-500">*</span></label>
            <input type="time" name="closing_time" value="{{ old('closing_time', $onboarding['step3']['closing_time'] ?? '00:00') }}" required
                class="w-full px-4 py-3 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading font-mono focus:outline-none focus:border-emerald-500">
        </div>
    </div>

    <!-- Trial Banner -->
    <div class="p-4 rounded-2xl bg-cyan-500/10 border border-cyan-500/20 flex items-center gap-3 text-xs">
        <div class="w-10 h-10 rounded-xl bg-cyan-500/20 text-cyan-400 flex items-center justify-center shrink-0 text-base">
            <i class="fa-solid fa-gift"></i>
        </div>
        <div>
            <div class="font-bold text-theme-heading">Includes 14-Day Free Platform Trial</div>
            <div class="text-[11px] text-theme-muted">Your first court includes a 14-day free trial on the platform. No upfront charges required.</div>
        </div>
    </div>

    <div class="pt-6 border-t border-stone-200 dark:border-stone-800 flex items-center justify-between">
        <a href="{{ route('owner.onboard.step2') }}" class="px-5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 text-theme-muted font-bold text-xs hover:bg-stone-100 dark:hover:bg-stone-800 transition-colors flex items-center gap-2">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back</span>
        </a>
        <button type="submit" class="px-6 py-3 rounded-xl bg-gradient-to-r from-emerald-500 to-cyan-500 hover:from-emerald-400 hover:to-cyan-400 text-slate-950 font-black text-xs shadow-lg shadow-emerald-500/20 flex items-center gap-2 transition-transform hover:scale-[1.02]">
            <span>Next: Payment Setup</span>
            <i class="fa-solid fa-arrow-right"></i>
        </button>
    </div>
</form>
@endsection
