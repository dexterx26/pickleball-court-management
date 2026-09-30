@extends('layouts.app')

@section('content')
<!-- Venue Header Hero -->
<section class="relative pt-6 pb-12 overflow-hidden border-b border-stone-200 dark:border-stone-800">
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-64 bg-gradient-to-b from-cyan-500/10 via-amber-600/10 to-transparent blur-3xl pointer-events-none -z-10"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <!-- Breadcrumb / Back Navigation -->
        <div class="flex items-center justify-between">
            <a href="{{ route('venues.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-cyan-600 dark:text-cyan-400 hover:underline">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Browse All Venues</span>
            </a>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Live Booking Channel: courts.{{ $venue->id }}</span>
                </span>
            </div>
        </div>

        <!-- Venue Banner Card -->
        <div class="rounded-3xl glass-card border border-stone-200 dark:border-stone-800 p-6 sm:p-8 relative overflow-hidden shadow-xl">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                <!-- Venue Logo & Details -->
                <div class="md:col-span-8 flex flex-col sm:flex-row items-start sm:items-center gap-5">
                    @if($venue->logo_url)
                        <img src="{{ $venue->logo_url }}" alt="{{ $venue->name }}" class="w-20 h-20 rounded-2xl object-cover border border-stone-200 dark:border-stone-700 shadow-md">
                    @else
                        <div class="w-20 h-20 rounded-2xl bg-gradient-to-tr from-amber-600 to-cyan-500 flex items-center justify-center text-white text-3xl font-black shadow-lg">
                            {{ $venue->prefix }}
                        </div>
                    @endif

                    <div class="space-y-1.5">
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-2xl sm:text-3xl font-black text-theme-heading tracking-tight">
                                {{ $venue->name }}
                            </h1>
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold uppercase bg-cyan-500/10 text-cyan-700 dark:text-cyan-400 border border-cyan-500/30">
                                {{ $venue->prefix }}
                            </span>
                        </div>

                        <p class="text-xs sm:text-sm text-theme-muted flex items-center gap-1.5">
                            <i class="fa-solid fa-location-dot text-amber-500"></i>
                            <span>{{ $venue->address ?: 'Pickleball Facility' }} • {{ $venue->city ?: 'Philippines' }}</span>
                        </p>

                        <div class="flex flex-wrap items-center gap-3 pt-1 text-xs text-theme-body font-medium">
                            <span class="flex items-center gap-1">
                                <i class="fa-solid fa-clock text-cyan-600"></i>
                                <span>{{ $settings ? \Carbon\Carbon::parse($settings->opening_time)->format('g:i A') . ' - ' . \Carbon\Carbon::parse($settings->closing_time)->format('g:i A') : '6:00 AM - 12:00 AM' }}</span>
                            </span>
                            <span>•</span>
                            <span class="flex items-center gap-1">
                                <i class="fa-solid fa-table-tennis-paddle-ball text-cyan-600"></i>
                                <span>{{ $courts->count() }} {{ Str::plural('Court', $courts->count()) }}</span>
                            </span>
                            <span>•</span>
                            <span class="font-bold text-amber-600 dark:text-amber-400">
                                {{ $venue->getPriceRangeLabel() }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Payment Mode & Fast Info -->
                <div class="md:col-span-4 flex flex-col sm:items-end justify-center space-y-2 border-t md:border-t-0 md:border-l border-stone-200 dark:border-stone-800 pt-4 md:pt-0 md:pl-6">
                    <div class="text-xs text-theme-muted">Payment Mode</div>
                    <span class="px-3 py-1 rounded-xl text-xs font-bold uppercase tracking-wider
                        @if($settings && $settings->payment_mode === 'paymongo')
                            bg-emerald-500/15 text-emerald-800 dark:text-emerald-300 border border-emerald-500/30
                        @elseif($settings && $settings->payment_mode === 'xendit')
                            bg-cyan-500/15 text-cyan-800 dark:text-cyan-300 border border-cyan-500/30
                        @else
                            bg-amber-500/15 text-amber-800 dark:text-amber-300 border border-amber-500/30
                        @endif">
                        @if($settings && $settings->payment_mode === 'paymongo')
                            <i class="fa-solid fa-bolt text-emerald-600 mr-1"></i> PayMongo Auto
                        @elseif($settings && $settings->payment_mode === 'xendit')
                            <i class="fa-solid fa-bolt text-cyan-600 mr-1"></i> Xendit Auto
                        @else
                            <i class="fa-solid fa-receipt text-amber-600 mr-1"></i> Manual GCash Receipt
                        @endif
                    </span>
                    <div class="text-[11px] text-theme-muted">
                        Hold timer: <strong>{{ max(1, (int) round(($settings->hold_timer_seconds ?? 120) / 60)) }} mins</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Dedicated Booking Engine -->
<section id="venue-booking-engine" class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        <!-- Date Selector & Status Legend -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-4 sm:p-5 rounded-3xl glass-card border border-stone-200 dark:border-stone-800 shadow-md">
            <!-- Date Filter -->
            <form action="{{ route('venues.show', $venue->slug) }}" method="GET" class="flex flex-wrap items-center gap-3">
                <label for="date-picker" class="text-xs font-bold text-theme-heading flex items-center gap-1.5">
                    <i class="fa-solid fa-calendar-day text-cyan-600"></i>
                    <span>Select Date:</span>
                </label>
                <input type="date" id="selectedDateInput" name="date" value="{{ $selectedDate }}" min="{{ now()->format('Y-m-d') }}"
                       onchange="this.form.submit()"
                       class="px-4 py-2 rounded-xl bg-stone-100 dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-xs font-bold text-theme-heading focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                <span class="text-xs text-theme-muted font-medium">
                    {{ \Carbon\Carbon::parse($selectedDate)->format('l, F j, Y') }}
                </span>
            </form>

            <!-- Legend -->
            <div class="flex flex-wrap items-center gap-4 text-xs font-semibold">
                <div class="flex items-center gap-1.5">
                    <span class="w-3.5 h-3.5 rounded-md bg-stone-100 dark:bg-stone-800 border border-stone-300 dark:border-stone-700"></span>
                    <span class="text-theme-body">Open</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-3.5 h-3.5 rounded-md bg-cyan-500 text-slate-950"></span>
                    <span class="text-cyan-700 dark:text-cyan-400">Selected</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-3.5 h-3.5 rounded-md bg-amber-500/20 border border-amber-500/40"></span>
                    <span class="text-amber-700 dark:text-amber-400">Held</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-3.5 h-3.5 rounded-md bg-rose-500/20 border border-rose-500/40"></span>
                    <span class="text-rose-700 dark:text-rose-400">Booked</span>
                </div>
            </div>
        </div>

        <!-- Booking Main Layout (Grid + Sidebar) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <!-- Courts & Slots Timeline (8 Cols) -->
            <div class="lg:col-span-8 space-y-6">
                @if(empty($availability))
                    <div class="p-12 text-center rounded-3xl glass-card border border-stone-200 dark:border-stone-800 space-y-3">
                        <i class="fa-solid fa-calendar-xmark text-3xl text-theme-muted"></i>
                        <h4 class="text-base font-bold text-theme-heading">No active courts available</h4>
                        <p class="text-xs text-theme-muted">This venue currently has no active courts listed for this date.</p>
                    </div>
                @else
                    @foreach($availability as $courtData)
                        @php $c = $courtData['court']; @endphp
                        <div id="booking-court-{{ $c['id'] }}" class="court-schedule-card p-5 sm:p-6 rounded-3xl glass-card border border-stone-200 dark:border-stone-800 shadow-xl transition-all" data-court-id="{{ $c['id'] }}">
                            <!-- Court Header -->
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-stone-200 dark:border-stone-800">
                                <div class="flex items-center gap-4">
                                    <div class="w-14 h-14 rounded-2xl bg-cyan-500/10 text-cyan-600 flex items-center justify-center text-xl font-black border border-cyan-500/30">
                                        #{{ $c['court_number'] ?? $loop->iteration }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h3 class="text-base sm:text-lg font-black text-theme-heading">{{ $c['name'] }}</h3>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ ($c['type'] ?? 'indoor') === 'indoor' ? 'bg-cyan-500/20 text-cyan-700 dark:text-cyan-300' : 'bg-amber-500/20 text-amber-700' }} uppercase">
                                                {{ ucfirst($c['type'] ?? 'indoor') }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-theme-muted mt-0.5">
                                            {{ $c['surface_type'] ?? 'Tournament Acrylic' }} • Max {{ $c['max_players'] ?? 4 }} Players
                                        </p>
                                    </div>
                                </div>
                                <div class="text-left sm:text-right">
                                    <div class="text-xs text-theme-muted">Rate</div>
                                    <div class="text-lg font-black text-cyan-600 dark:text-cyan-400">₱{{ number_format($c['price_per_hour'], 0) }}<span class="text-xs text-theme-muted font-normal">/hr</span></div>
                                </div>
                            </div>

                            <!-- Slots Grid -->
                            <div class="mt-4">
                                <div class="text-xs font-semibold text-theme-muted mb-2 flex items-center justify-between">
                                    <span>Available 1-Hour Time Slots:</span>
                                    <span class="text-[11px] text-amber-600 dark:text-amber-400 font-bold">Click slots to select</span>
                                </div>
                                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2 court-slots-grid" data-court-id="{{ $c['id'] }}" data-court-name="{{ $c['name'] }}" data-rate="{{ $c['price_per_hour'] }}">
                                    @foreach($courtData['slots'] as $slot)
                                        @php
                                            $status = $slot['status'];
                                            $isAvailable = $status === 'available';
                                        @endphp
                                        <button type="button"
                                            class="court-slot p-2.5 rounded-xl text-center text-xs font-semibold transition-all relative border
                                                {{ $isAvailable ? 'bg-stone-100 dark:bg-stone-900 border-stone-200 dark:border-stone-800 text-theme-heading hover:border-cyan-500 cursor-pointer' : '' }}
                                                {{ $status === 'held' ? 'bg-amber-500/10 border-amber-500/30 text-amber-700 dark:text-amber-300 cursor-not-allowed select-none opacity-80' : '' }}
                                                {{ $status === 'pending_approval' ? 'bg-indigo-500/10 border-indigo-500/30 text-indigo-700 dark:text-indigo-300 cursor-not-allowed select-none opacity-80' : '' }}
                                                {{ $status === 'confirmed' ? 'bg-rose-500/10 border-rose-500/30 text-rose-700 dark:text-rose-400 cursor-not-allowed select-none opacity-80' : '' }}"
                                            data-court-id="{{ $c['id'] }}"
                                            data-slot-time="{{ $slot['time'] }}"
                                            data-display-time="{{ $slot['display_time'] }}"
                                            data-display-range="{{ $slot['display_range'] ?? $slot['display_time'] }}"
                                            data-status="{{ $status }}"
                                            data-remaining-seconds="{{ $slot['remaining_seconds'] ?? 0 }}">
                                            <div class="font-bold text-xs">{{ $slot['display_time'] }}</div>
                                            <div class="text-[10px] opacity-85 mt-0.5 slot-status-label">
                                                @if($status === 'available')
                                                    <span class="text-cyan-600 dark:text-cyan-400 font-bold">Open</span>
                                                @elseif($status === 'held')
                                                    <span class="text-amber-700 dark:text-amber-300 flex items-center justify-center gap-1">
                                                        <i class="fa-regular fa-hourglass-half text-[9px] animate-spin"></i>
                                                        <span class="slot-timer" data-seconds="{{ $slot['remaining_seconds'] }}">{{ floor($slot['remaining_seconds'] / 60) }}:{{ sprintf('%02d', $slot['remaining_seconds'] % 60) }}</span>
                                                    </span>
                                                @elseif($status === 'pending_approval')
                                                    <span class="text-indigo-700 dark:text-indigo-300">In Review</span>
                                                @elseif($status === 'confirmed')
                                                    <span class="text-rose-700 dark:text-rose-400">Booked</span>
                                                @endif
                                            </div>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <!-- Booking Summary & Guest Form (4 Cols Sticky) -->
            <div class="lg:col-span-4 sticky top-24">
                <div class="p-6 rounded-3xl glass-card border border-stone-200 dark:border-stone-800 shadow-2xl relative overflow-hidden space-y-5">
                    <div class="flex items-center justify-between pb-3 border-b border-stone-200 dark:border-stone-800">
                        <h3 class="text-base font-extrabold text-theme-heading flex items-center gap-2">
                            <i class="fa-solid fa-receipt text-cyan-600"></i>
                            <span>Reservation Summary</span>
                        </h3>
                    </div>

                    <!-- Placeholder when no slot selected -->
                    <div id="noSelectionNotice" class="py-10 text-center space-y-2">
                        <div class="w-12 h-12 rounded-2xl bg-stone-200 dark:bg-stone-800 text-theme-muted flex items-center justify-center text-xl mx-auto">
                            <i class="fa-solid fa-arrow-pointer"></i>
                        </div>
                        <h4 class="text-sm font-bold text-theme-heading">No Timeslots Selected</h4>
                        <p class="text-xs text-theme-muted">Click any open timeslot above to begin your reservation.</p>
                    </div>

                    <!-- Active Selection Box -->
                    <div id="activeSelectionBox" class="hidden space-y-4">
                        <div class="p-4 rounded-2xl bg-stone-100 dark:bg-stone-900 border border-stone-200 dark:border-stone-800 space-y-2 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="text-theme-muted">Court:</span>
                                <span id="summaryCourtName" class="font-bold text-theme-heading">-</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-theme-muted">Date:</span>
                                <span id="summaryDate" class="font-bold text-theme-heading">{{ $selectedDate }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-theme-muted">Slots Selected:</span>
                                <span id="summarySlotsList" class="font-semibold text-theme-heading text-right">-</span>
                            </div>
                            <div class="pt-2 border-t border-stone-200 dark:border-stone-800 flex items-center justify-between text-sm">
                                <span class="font-bold text-theme-heading">Total Amount:</span>
                                <span id="summaryTotalAmount" class="font-black text-cyan-600 dark:text-cyan-400 text-lg">₱0.00</span>
                            </div>
                        </div>

                        <!-- Customer Details Form -->
                        <div class="space-y-3">
                            <div>
                                <label class="block text-[11px] font-bold text-theme-heading mb-1">Your Full Name <span class="text-rose-500">*</span></label>
                                <input type="text" id="custName" required placeholder="e.g. Juan dela Cruz"
                                       value="{{ Auth::check() ? Auth::user()->name : '' }}"
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-stone-100 dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-xs font-semibold text-theme-heading focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-theme-heading mb-1">Mobile Phone <span class="text-rose-500">*</span></label>
                                <input type="tel" id="custPhone" required placeholder="0917XXXXXXX"
                                       value="{{ Auth::check() ? Auth::user()->phone : '' }}"
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-stone-100 dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-xs font-semibold text-theme-heading focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-theme-heading mb-1">Email (Optional)</label>
                                <input type="email" id="custEmail" placeholder="juan@example.com"
                                       value="{{ Auth::check() ? Auth::user()->email : '' }}"
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-stone-100 dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-xs font-semibold text-theme-heading focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                            </div>
                        </div>

                        <!-- Action Buttons based on Payment Mode -->
                        @if($settings && in_array($settings->payment_mode, ['paymongo', 'xendit']))
                            <button type="button" id="btnHoldAndPay" onclick="handleOnlineHold()"
                                    class="w-full py-3.5 px-4 rounded-2xl bg-gradient-to-r from-cyan-500 to-cyan-600 hover:from-cyan-400 hover:to-cyan-500 text-slate-950 font-black text-xs shadow-xl shadow-cyan-500/20 flex items-center justify-center gap-2 transition-all">
                                <i class="fa-solid fa-lock"></i>
                                <span>Hold & Pay with {{ ucfirst($settings->payment_mode) }}</span>
                            </button>
                        @else
                            <button type="button" onclick="openManualReceiptModal()"
                                    class="w-full py-3.5 px-4 rounded-2xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-black text-xs shadow-xl shadow-amber-500/20 flex items-center justify-center gap-2 transition-all">
                                <i class="fa-solid fa-upload"></i>
                                <span>Upload GCash Receipt</span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Manual Receipt Modal -->
<div id="manualReceiptModal" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="rounded-3xl glass-card border border-stone-200 dark:border-stone-800 max-w-md w-full p-6 space-y-4 shadow-2xl">
        <div class="flex items-center justify-between pb-3 border-b border-stone-200 dark:border-stone-800">
            <h3 class="text-base font-black text-theme-heading flex items-center gap-2">
                <i class="fa-solid fa-receipt text-amber-500"></i>
                <span>Submit Payment Receipt</span>
            </h3>
            <button type="button" onclick="closeManualReceiptModal()" class="text-theme-muted hover:text-theme-heading">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        @if($settings && $settings->gcash_qr_path)
            <div class="text-center p-4 rounded-2xl bg-stone-100 dark:bg-stone-900 border border-stone-200 dark:border-stone-800 space-y-2">
                <div class="text-xs font-bold text-theme-heading">Scan GCash QR to Pay:</div>
                <img src="{{ asset('storage/' . $settings->gcash_qr_path) }}" alt="GCash QR" class="w-40 h-40 object-contain mx-auto rounded-xl border">
                @if($settings->gcash_number)
                    <div class="text-xs font-mono font-bold text-amber-600">{{ $settings->gcash_name }} ({{ $settings->gcash_number }})</div>
                @endif
            </div>
        @endif

        <form action="{{ route('booking.manual_receipt') }}" method="POST" enctype="multipart/form-data" id="manualReceiptForm" class="space-y-4">
            @csrf
            <input type="hidden" name="court_id" id="mrCourtId">
            <input type="hidden" name="date" id="mrDate" value="{{ $selectedDate }}">
            <input type="hidden" name="slots" id="mrSlots">
            <input type="hidden" name="customer_name" id="mrCustName">
            <input type="hidden" name="customer_phone" id="mrCustPhone">
            <input type="hidden" name="customer_email" id="mrCustEmail">

            <div>
                <label class="block text-xs font-bold text-theme-heading mb-1.5">Upload Receipt Screenshot (GCash / Maya) <span class="text-rose-500">*</span></label>
                <input type="file" name="receipt" accept="image/*" required
                       class="w-full text-xs text-theme-body file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-cyan-500 file:text-slate-950 hover:file:bg-cyan-400">
            </div>

            <button type="submit" class="w-full py-3 rounded-2xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs shadow-lg transition-all flex items-center justify-center gap-2">
                <i class="fa-solid fa-paper-plane"></i>
                <span>Submit Reservation Request</span>
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const VENUE_ID = {{ $venue->id }};
    const bookingState = {
        selectedCourtId: null,
        selectedCourtName: null,
        ratePerHour: 0,
        selectedSlots: [],
    };

    // Slot click handler
    document.querySelectorAll('.court-slot').forEach(btn => {
        btn.addEventListener('click', () => {
            const status = btn.getAttribute('data-status');
            if (status !== 'available' && !btn.classList.contains('slot-selected')) {
                return;
            }

            const courtId = parseInt(btn.getAttribute('data-court-id'));
            const slotTime = btn.getAttribute('data-slot-time');
            const courtGrid = btn.closest('.court-slots-grid');
            const courtName = courtGrid.getAttribute('data-court-name');
            const rate = parseFloat(courtGrid.getAttribute('data-rate'));

            if (bookingState.selectedCourtId && bookingState.selectedCourtId !== courtId) {
                // Clear selection from other court
                document.querySelectorAll('.court-slot.slot-selected').forEach(s => {
                    s.classList.remove('slot-selected', 'bg-cyan-500', 'text-slate-950');
                    s.classList.add('bg-stone-100', 'dark:bg-stone-900');
                    s.querySelector('.slot-status-label').innerHTML = '<span class="text-cyan-600 dark:text-cyan-400 font-bold">Open</span>';
                });
                bookingState.selectedSlots = [];
            }

            bookingState.selectedCourtId = courtId;
            bookingState.selectedCourtName = courtName;
            bookingState.ratePerHour = rate;

            const index = bookingState.selectedSlots.indexOf(slotTime);
            if (index > -1) {
                bookingState.selectedSlots.splice(index, 1);
                btn.classList.remove('slot-selected', 'bg-cyan-500', 'text-slate-950');
                btn.classList.add('bg-stone-100', 'dark:bg-stone-900');
                btn.querySelector('.slot-status-label').innerHTML = '<span class="text-cyan-600 dark:text-cyan-400 font-bold">Open</span>';
            } else {
                bookingState.selectedSlots.push(slotTime);
                bookingState.selectedSlots.sort();
                btn.classList.add('slot-selected', 'bg-cyan-500', 'text-slate-950');
                btn.classList.remove('bg-stone-100', 'dark:bg-stone-900');
                btn.querySelector('.slot-status-label').innerHTML = '<span class="text-slate-950 font-black">Selected</span>';
            }

            updateSummaryUI();
        });
    });

    function updateSummaryUI() {
        const noSel = document.getElementById('noSelectionNotice');
        const activeSel = document.getElementById('activeSelectionBox');

        if (bookingState.selectedSlots.length === 0) {
            noSel.classList.remove('hidden');
            activeSel.classList.add('hidden');
            return;
        }

        noSel.classList.add('hidden');
        activeSel.classList.remove('hidden');

        document.getElementById('summaryCourtName').textContent = bookingState.selectedCourtName;
        document.getElementById('summarySlotsList').textContent = bookingState.selectedSlots.join(', ');
        const total = bookingState.selectedSlots.length * bookingState.ratePerHour;
        document.getElementById('summaryTotalAmount').textContent = '₱' + total.toLocaleString('en-US', { minimumFractionDigits: 2 });
    }

    async function handleOnlineHold() {
        const name = document.getElementById('custName').value.trim();
        const phone = document.getElementById('custPhone').value.trim();
        const email = document.getElementById('custEmail').value.trim();

        if (!name || !phone) {
            Swal.fire({
                icon: 'warning',
                title: 'Missing Details',
                text: 'Please enter your Full Name and Mobile Phone Number.',
                confirmButtonColor: '#06b6d4',
            });
            return;
        }

        const btn = document.getElementById('btnHoldAndPay');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Locking Slots...';

        try {
            const res = await fetch('/api/hold-slots', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    court_id: bookingState.selectedCourtId,
                    date: document.getElementById('selectedDateInput').value,
                    slots: bookingState.selectedSlots,
                    customer_name: name,
                    customer_phone: phone,
                    customer_email: email,
                })
            });

            const data = await res.json();
            if (data.success && data.invoice_url) {
                window.location.href = data.invoice_url;
            } else if (data.success && data.track_url) {
                window.location.href = data.track_url;
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Hold Failed',
                    text: data.message || 'Unable to hold slots. They may have been booked just now.',
                    confirmButtonColor: '#06b6d4',
                });
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-lock"></i> Hold & Pay';
            }
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Connection failed. Please try again.' });
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-lock"></i> Hold & Pay';
        }
    }

    function openManualReceiptModal() {
        const name = document.getElementById('custName').value.trim();
        const phone = document.getElementById('custPhone').value.trim();
        const email = document.getElementById('custEmail').value.trim();

        if (!name || !phone) {
            Swal.fire({
                icon: 'warning',
                title: 'Missing Details',
                text: 'Please enter your Full Name and Mobile Phone Number.',
                confirmButtonColor: '#f59e0b',
            });
            return;
        }

        document.getElementById('mrCourtId').value = bookingState.selectedCourtId;
        document.getElementById('mrSlots').value = JSON.stringify(bookingState.selectedSlots);
        document.getElementById('mrCustName').value = name;
        document.getElementById('mrCustPhone').value = phone;
        document.getElementById('mrCustEmail').value = email;

        document.getElementById('manualReceiptModal').classList.remove('hidden');
    }

    function closeManualReceiptModal() {
        document.getElementById('manualReceiptModal').classList.add('hidden');
    }

    // Subscribe to venue-specific isolated Reverb channel
    if (typeof window.subscribeCourtUpdates === 'function') {
        window.subscribeCourtUpdates((data) => {
            const curDate = document.getElementById('selectedDateInput')?.value;
            if (data.date === curDate) {
                // If slots belong to this court, update their UI
                (data.slots || []).forEach(slotTime => {
                    const slotBtn = document.querySelector(`.court-slot[data-court-id="${data.court_id}"][data-slot-time="${slotTime}"]`);
                    if (slotBtn) {
                        slotBtn.setAttribute('data-status', data.status);
                        if (data.status === 'confirmed') {
                            slotBtn.className = 'court-slot p-2.5 rounded-xl text-center text-xs font-semibold bg-rose-500/10 border border-rose-500/30 text-rose-700 dark:text-rose-400 cursor-not-allowed select-none opacity-80';
                            slotBtn.querySelector('.slot-status-label').innerHTML = '<span class="text-rose-700 dark:text-rose-400">Booked</span>';
                        } else if (data.status === 'available') {
                            slotBtn.className = 'court-slot p-2.5 rounded-xl text-center text-xs font-semibold bg-stone-100 dark:bg-stone-900 border border-stone-200 dark:border-stone-800 text-theme-heading hover:border-cyan-500 cursor-pointer';
                            slotBtn.querySelector('.slot-status-label').innerHTML = '<span class="text-cyan-600 dark:text-cyan-400 font-bold">Open</span>';
                        }
                    }
                });
            }
        }, VENUE_ID);
    }
</script>
@endpush
@endsection
