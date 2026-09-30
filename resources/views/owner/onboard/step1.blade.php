@extends('owner.onboard.layout', ['step' => 1])

@section('onboard-step')
<form action="{{ route('owner.onboard.post_step1') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
    @csrf

    <div class="border-b border-stone-200 dark:border-stone-800 pb-4">
        <h2 class="text-xl font-black text-theme-heading flex items-center gap-2.5">
            <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-500 flex items-center justify-center text-sm font-black">1</span>
            Venue Information & Branding
        </h2>
        <p class="text-xs text-theme-muted mt-1">Provide your sports center's public identity, branding, and contact channels.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 text-xs">
        <div class="sm:col-span-2">
            <label class="block font-bold text-theme-body mb-1">Venue / Facility Name <span class="text-rose-500">*</span></label>
            <input type="text" name="name" value="{{ old('name', $onboarding['step1']['name'] ?? '') }}" required
                placeholder="e.g. Paddle Field Sports Center"
                class="w-full px-4 py-3 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading font-medium placeholder-stone-400 focus:outline-none focus:border-emerald-500">
            @error('name') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block font-bold text-theme-body mb-1">Tagline</label>
            <input type="text" name="tagline" value="{{ old('tagline', $onboarding['step1']['tagline'] ?? '') }}"
                placeholder="e.g. Premier Indoor & Outdoor Facility"
                class="w-full px-4 py-3 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading placeholder-stone-400 focus:outline-none focus:border-emerald-500">
        </div>

        <div>
            <label class="block font-bold text-theme-body mb-1">Booking Reference Prefix <span class="text-rose-500">*</span></label>
            <div class="flex items-center gap-2">
                <input type="text" name="booking_ref_prefix" value="{{ old('booking_ref_prefix', $onboarding['step1']['booking_ref_prefix'] ?? 'PF') }}" required maxlength="5"
                    placeholder="e.g. PF, SM, BG"
                    class="w-32 px-4 py-3 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading uppercase font-mono font-bold text-center focus:outline-none focus:border-emerald-500">
                <span class="text-[11px] text-theme-muted">Unique 2–5 letters for passes (e.g. PF-20261001-XXXX)</span>
            </div>
            @error('booking_ref_prefix') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block font-bold text-theme-body mb-1">Contact Phone <span class="text-rose-500">*</span></label>
            <input type="tel" name="phone" value="{{ old('phone', $onboarding['step1']['phone'] ?? auth()->user()->phone ?? '') }}" required
                placeholder="+63 917 555 7233"
                class="w-full px-4 py-3 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading placeholder-stone-400 focus:outline-none focus:border-emerald-500">
            @error('phone') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block font-bold text-theme-body mb-1">Official Email <span class="text-rose-500">*</span></label>
            <input type="email" name="email" value="{{ old('email', $onboarding['step1']['email'] ?? auth()->user()->email ?? '') }}" required
                placeholder="contact@sportsfacility.com"
                class="w-full px-4 py-3 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading placeholder-stone-400 focus:outline-none focus:border-emerald-500">
            @error('email') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div class="sm:col-span-2">
            <label class="block font-bold text-theme-body mb-1">Street Address <span class="text-rose-500">*</span></label>
            <input type="text" name="address" value="{{ old('address', $onboarding['step1']['address'] ?? '') }}" required
                placeholder="e.g. Bacal 3, Maharlika Highway"
                class="w-full px-4 py-3 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading placeholder-stone-400 focus:outline-none focus:border-emerald-500">
            @error('address') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block font-bold text-theme-body mb-1">City / Municipality <span class="text-rose-500">*</span></label>
            <input type="text" name="city" value="{{ old('city', $onboarding['step1']['city'] ?? '') }}" required
                placeholder="e.g. Talavera, Cabanatuan, Quezon City"
                class="w-full px-4 py-3 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading placeholder-stone-400 focus:outline-none focus:border-emerald-500">
            @error('city') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block font-bold text-theme-body mb-1">Province / State <span class="text-rose-500">*</span></label>
            <input type="text" name="province" value="{{ old('province', $onboarding['step1']['province'] ?? '') }}" required
                placeholder="e.g. Nueva Ecija, Metro Manila, Pampanga"
                class="w-full px-4 py-3 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading placeholder-stone-400 focus:outline-none focus:border-emerald-500">
            @error('province') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div class="sm:col-span-2">
            <label class="block font-bold text-theme-body mb-1">Venue Description</label>
            <textarea name="description" rows="3" placeholder="Tell players about your facility, court surfaces, lighting, lounges, and parking..."
                class="w-full px-4 py-3 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading placeholder-stone-400 focus:outline-none focus:border-emerald-500">{{ old('description', $onboarding['step1']['description'] ?? '') }}</textarea>
        </div>

        <div>
            <label class="block font-bold text-theme-body mb-1">Venue Logo (Square)</label>
            <input type="file" name="logo" accept="image/*"
                class="w-full px-3 py-2 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-body file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-500/10 file:text-emerald-500">
        </div>

        <div>
            <label class="block font-bold text-theme-body mb-1">Cover Photo / Banner</label>
            <input type="file" name="cover_image" accept="image/*"
                class="w-full px-3 py-2 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-body file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-500/10 file:text-emerald-500">
        </div>
    </div>

    <div class="pt-6 border-t border-stone-200 dark:border-stone-800 flex items-center justify-between">
        <a href="{{ route('home') }}" class="px-5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 text-theme-muted font-bold text-xs hover:bg-stone-100 dark:hover:bg-stone-800 transition-colors">
            Cancel
        </a>
        <button type="submit" class="px-6 py-3 rounded-xl bg-gradient-to-r from-emerald-500 to-cyan-500 hover:from-emerald-400 hover:to-cyan-400 text-slate-950 font-black text-xs shadow-lg shadow-emerald-500/20 flex items-center gap-2 transition-transform hover:scale-[1.02]">
            <span>Next: Location Pin</span>
            <i class="fa-solid fa-arrow-right"></i>
        </button>
    </div>
</form>
@endsection
