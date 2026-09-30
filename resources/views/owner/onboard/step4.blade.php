@extends('owner.onboard.layout', ['step' => 4])

@section('onboard-step')
<form action="{{ route('owner.onboard.post_step4') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
    @csrf

    <div class="border-b border-stone-200 dark:border-stone-800 pb-4">
        <h2 class="text-xl font-black text-theme-heading flex items-center gap-2.5">
            <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-500 flex items-center justify-center text-sm font-black">4</span>
            Payment Gateway & Booking Rules
        </h2>
        <p class="text-xs text-theme-muted mt-1">Choose how players pay when booking your courts. You can switch or update credentials anytime.</p>
    </div>

    <!-- Payment Mode Selection -->
    <div>
        <label class="block font-bold text-theme-body mb-2 text-xs">Primary Payment Collection Mode <span class="text-rose-500">*</span></label>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <label class="p-4 rounded-2xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-900 cursor-pointer hover:border-emerald-500 transition-colors has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-500/5">
                <input type="radio" name="payment_mode" value="manual_receipt" {{ old('payment_mode', $onboarding['step4']['payment_mode'] ?? 'manual_receipt') === 'manual_receipt' ? 'checked' : '' }} class="sr-only" onchange="togglePaymentFields(this.value)">
                <div class="flex items-center gap-2 font-bold text-theme-heading text-xs">
                    <i class="fa-solid fa-qrcode text-emerald-500 text-sm"></i>
                    <span>Manual Receipt</span>
                </div>
                <p class="text-[11px] text-theme-muted mt-1">Players send payment via GCash/Maya and upload proof screenshot for your approval.</p>
            </label>

            <label class="p-4 rounded-2xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-900 cursor-pointer hover:border-cyan-500 transition-colors has-[:checked]:border-cyan-500 has-[:checked]:bg-cyan-500/5">
                <input type="radio" name="payment_mode" value="paymongo" {{ old('payment_mode', $onboarding['step4']['payment_mode'] ?? '') === 'paymongo' ? 'checked' : '' }} class="sr-only" onchange="togglePaymentFields(this.value)">
                <div class="flex items-center gap-2 font-bold text-theme-heading text-xs">
                    <i class="fa-solid fa-bolt text-cyan-500 text-sm"></i>
                    <span>PayMongo</span>
                </div>
                <p class="text-[11px] text-theme-muted mt-1">Automated GCash, Maya, and Card checkout sessions with instant confirmation.</p>
            </label>

            <label class="p-4 rounded-2xl border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-900 cursor-pointer hover:border-purple-500 transition-colors has-[:checked]:border-purple-500 has-[:checked]:bg-purple-500/5">
                <input type="radio" name="payment_mode" value="xendit" {{ old('payment_mode', $onboarding['step4']['payment_mode'] ?? '') === 'xendit' ? 'checked' : '' }} class="sr-only" onchange="togglePaymentFields(this.value)">
                <div class="flex items-center gap-2 font-bold text-theme-heading text-xs">
                    <i class="fa-solid fa-wallet text-purple-500 text-sm"></i>
                    <span>Xendit Gateway</span>
                </div>
                <p class="text-[11px] text-theme-muted mt-1">Multi-channel online invoices with real-time webhooks.</p>
            </label>
        </div>
    </div>

    <!-- Manual Receipt Fields -->
    <div id="manualFields" class="p-5 rounded-2xl bg-stone-50 dark:bg-stone-900/60 border border-stone-200 dark:border-stone-800 space-y-4 text-xs">
        <h3 class="font-bold text-theme-heading text-xs flex items-center gap-2">
            <i class="fa-solid fa-mobile-screen-button text-emerald-500"></i> GCash / Maya / Bank Account Details
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block font-bold text-theme-body mb-1">Bank / Wallet Provider</label>
                <input type="text" name="manual_bank_name" value="{{ old('manual_bank_name', $onboarding['step4']['manual_bank_name'] ?? 'GCash / Maya / BDO') }}"
                    placeholder="e.g. GCash / Maya"
                    class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading placeholder-stone-400 focus:outline-none focus:border-emerald-500">
            </div>

            <div>
                <label class="block font-bold text-theme-body mb-1">Account Holder Name</label>
                <input type="text" name="manual_account_name" value="{{ old('manual_account_name', $onboarding['step4']['manual_account_name'] ?? auth()->user()->name) }}"
                    placeholder="e.g. Juan Dela Cruz"
                    class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading placeholder-stone-400 focus:outline-none focus:border-emerald-500">
            </div>

            <div>
                <label class="block font-bold text-theme-body mb-1">Account / Mobile Number</label>
                <input type="text" name="manual_account_number" value="{{ old('manual_account_number', $onboarding['step4']['manual_account_number'] ?? auth()->user()->phone ?? '0917-555-7233') }}"
                    placeholder="e.g. 0917-555-7233"
                    class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading placeholder-stone-400 focus:outline-none focus:border-emerald-500">
            </div>

            <div>
                <label class="block font-bold text-theme-body mb-1">QR Standee Image (Optional)</label>
                <input type="file" name="manual_payment_qr" accept="image/*"
                    class="w-full px-3 py-2 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-body file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-500/10 file:text-emerald-500">
            </div>
        </div>

        <div>
            <label class="block font-bold text-theme-body mb-1">Player Instructions</label>
            <textarea name="manual_payment_instructions" rows="2"
                placeholder="1. Send reservation amount to GCash. 2. Mention booking reference in transfer note. 3. Upload screenshot."
                class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading placeholder-stone-400 focus:outline-none focus:border-emerald-500">{{ old('manual_payment_instructions', $onboarding['step4']['manual_payment_instructions'] ?? "1. Send exact booking total via GCash or Maya.\n2. In the payment note, indicate your Booking Reference Code.\n3. Take a screenshot and upload below.") }}</textarea>
        </div>
    </div>

    <!-- Slot Holding Timer -->
    <div class="p-4 rounded-2xl bg-stone-50 dark:bg-stone-900/60 border border-stone-200 dark:border-stone-800 space-y-2 text-xs">
        <div class="flex items-center justify-between">
            <label class="font-bold text-theme-heading">Temporary Hold Countdown Timer</label>
            <span id="holdingDurationLabel" class="font-mono font-bold text-emerald-500">120 seconds (2 mins)</span>
        </div>
        <input type="range" name="holding_duration_seconds" id="holdingSlider" min="60" max="600" step="30"
            value="{{ old('holding_duration_seconds', $onboarding['step4']['holding_duration_seconds'] ?? 120) }}"
            class="w-full accent-emerald-500 cursor-pointer" oninput="updateHoldingLabel(this.value)">
        <p class="text-[11px] text-theme-muted">
            When a player clicks checkout, their selected timeslots are held on the live Reverb calendar so no other player can take them while paying.
        </p>
    </div>

    <div class="pt-6 border-t border-stone-200 dark:border-stone-800 flex items-center justify-between">
        <a href="{{ route('owner.onboard.step3') }}" class="px-5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 text-theme-muted font-bold text-xs hover:bg-stone-100 dark:hover:bg-stone-800 transition-colors flex items-center gap-2">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back</span>
        </a>
        <button type="submit" class="px-6 py-3 rounded-xl bg-gradient-to-r from-emerald-500 to-cyan-500 hover:from-emerald-400 hover:to-cyan-400 text-slate-950 font-black text-xs shadow-lg shadow-emerald-500/20 flex items-center gap-2 transition-transform hover:scale-[1.02]">
            <span>Next: Review & Launch</span>
            <i class="fa-solid fa-arrow-right"></i>
        </button>
    </div>
</form>

<script>
    function updateHoldingLabel(val) {
        const mins = Math.floor(val / 60);
        const secs = val % 60;
        let str = val + ' seconds';
        if (mins > 0) {
            str += ` (${mins} min${mins > 1 ? 's' : ''}${secs > 0 ? ' ' + secs + 's' : ''})`;
        }
        document.getElementById('holdingDurationLabel').innerText = str;
    }

    function togglePaymentFields(mode) {
        const manualFields = document.getElementById('manualFields');
        if (mode === 'manual_receipt') {
            manualFields.classList.remove('hidden');
        } else {
            manualFields.classList.add('hidden');
        }
    }
</script>
@endsection
