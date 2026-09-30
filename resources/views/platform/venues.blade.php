@extends('layouts.app')

@section('content')
<div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-500/10 border border-cyan-500/30 text-xs font-semibold text-cyan-600 dark:text-cyan-400 mb-2">
                <i class="fa-solid fa-shield-halved"></i>
                <span>Platform Superadmin Portal</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-theme-heading tracking-tight">
                Venue Directory & Approvals
            </h1>
            <p class="text-xs text-theme-muted mt-1">
                Review newly onboarded pickleball venues, verify facility specifications, and authorize public listing.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('platform.subscriptions') }}" class="px-4 py-2.5 rounded-xl bg-stone-200 dark:bg-stone-800 hover:bg-stone-300 text-xs font-bold text-theme-heading border border-stone-300 dark:border-stone-700 flex items-center gap-2 transition-all">
                <i class="fa-solid fa-coins text-amber-500"></i>
                <span>Platform Subscriptions</span>
            </a>
            <a href="{{ route('venues.index') }}" target="_blank" class="px-4 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 text-xs font-black shadow-md flex items-center gap-2 transition-all">
                <i class="fa-solid fa-globe"></i>
                <span>Public Discovery</span>
            </a>
        </div>
    </div>

    <!-- Stats & Filters -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        <!-- Filter Tabs -->
        <div class="flex flex-wrap items-center gap-2 p-1.5 rounded-2xl glass-card border border-stone-200 dark:border-stone-800">
            <a href="{{ route('platform.venues', ['status' => 'all', 'search' => $search]) }}"
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ $status === 'all' ? 'bg-cyan-500 text-slate-950 shadow-md' : 'text-theme-body hover:bg-stone-200 dark:hover:bg-stone-800' }}">
                All ({{ $counts['all'] }})
            </a>
            <a href="{{ route('platform.venues', ['status' => 'pending', 'search' => $search]) }}"
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ $status === 'pending' ? 'bg-amber-500 text-slate-950 shadow-md' : 'text-theme-body hover:bg-stone-200 dark:hover:bg-stone-800' }}">
                Pending ({{ $counts['pending'] }})
            </a>
            <a href="{{ route('platform.venues', ['status' => 'approved', 'search' => $search]) }}"
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ $status === 'approved' ? 'bg-emerald-500 text-slate-950 shadow-md' : 'text-theme-body hover:bg-stone-200 dark:hover:bg-stone-800' }}">
                Approved ({{ $counts['approved'] }})
            </a>
            <a href="{{ route('platform.venues', ['status' => 'rejected', 'search' => $search]) }}"
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ $status === 'rejected' ? 'bg-rose-500 text-cascading-white shadow-md' : 'text-theme-body hover:bg-stone-200 dark:hover:bg-stone-800' }}">
                Rejected ({{ $counts['rejected'] }})
            </a>
        </div>

        <!-- Search Bar -->
        <form action="{{ route('platform.venues') }}" method="GET" class="relative">
            <input type="hidden" name="status" value="{{ $status }}">
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-theme-muted text-xs"></i>
            <input type="text" name="search" value="{{ $search }}" placeholder="Search venue, city, owner..."
                   class="pl-9 pr-4 py-2 rounded-xl bg-stone-100 dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-xs font-semibold text-theme-heading focus:ring-2 focus:ring-cyan-500 focus:outline-none">
        </form>
    </div>

    <!-- Venues Table Card -->
    <div class="rounded-3xl glass-card border border-stone-200 dark:border-stone-800 overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-stone-100/80 dark:bg-stone-900/80 border-b border-stone-200 dark:border-stone-800 text-[11px] font-bold text-theme-muted uppercase tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4">Venue Details</th>
                        <th class="py-3.5 px-4">Court Owner</th>
                        <th class="py-3.5 px-4">Location</th>
                        <th class="py-3.5 px-4">Courts</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200 dark:divide-stone-800">
                    @forelse($venues as $v)
                        <tr class="hover:bg-cyan-500/5 transition-colors">
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-600 to-cyan-500 flex items-center justify-center text-cascading-white font-black text-sm shadow-md">
                                        {{ $v->prefix }}
                                    </div>
                                    <div>
                                        <div class="font-black text-theme-heading text-sm">{{ $v->name }}</div>
                                        <div class="text-[11px] text-theme-muted font-mono">/venues/{{ $v->slug }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <div class="font-bold text-theme-heading">{{ $v->owner?->name ?? 'Unassigned' }}</div>
                                <div class="text-[11px] text-theme-muted">{{ $v->owner?->email ?? '—' }}</div>
                            </td>
                            <td class="py-4 px-4">
                                <div class="font-semibold text-theme-heading">{{ $v->city ?: 'Philippines' }}</div>
                                <div class="text-[11px] text-theme-muted">{{ $v->province ?: 'Region' }}</div>
                            </td>
                            <td class="py-4 px-4">
                                <span class="px-2.5 py-1 rounded-lg bg-cyan-500/10 text-cyan-700 dark:text-cyan-300 font-bold text-xs border border-cyan-500/20">
                                    {{ $v->courts_count }} {{ Str::plural('Court', $v->courts_count) }}
                                </span>
                            </td>
                            <td class="py-4 px-4">
                                @if($v->status === 'approved')
                                    <span class="px-2.5 py-1 rounded-full bg-emerald-500/15 text-emerald-700 dark:text-emerald-400 font-bold text-[10px] uppercase border border-emerald-500/30">
                                        Approved
                                    </span>
                                @elseif($v->status === 'pending')
                                    <span class="px-2.5 py-1 rounded-full bg-amber-500/15 text-amber-700 dark:text-amber-400 font-bold text-[10px] uppercase border border-amber-500/30 animate-pulse">
                                        Pending Review
                                    </span>
                                @elseif($v->status === 'rejected')
                                    <span class="px-2.5 py-1 rounded-full bg-rose-500/15 text-rose-700 dark:text-rose-400 font-bold text-[10px] uppercase border border-rose-500/30">
                                        Rejected
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full bg-stone-500/15 text-stone-600 dark:text-stone-400 font-bold text-[10px] uppercase">
                                        {{ $v->status }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if($v->status !== 'approved')
                                        <form action="{{ route('platform.venues.approve', $v->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs shadow-sm flex items-center gap-1 transition-all">
                                                <i class="fa-solid fa-check"></i>
                                                <span>Approve</span>
                                            </button>
                                        </form>
                                    @endif

                                    @if($v->status !== 'rejected')
                                        <button type="button" onclick="openRejectModal({{ $v->id }}, '{{ addslashes($v->name) }}')"
                                                class="px-3 py-1.5 rounded-xl bg-rose-500/15 hover:bg-rose-500 text-rose-600 hover:text-white font-bold text-xs border border-rose-500/30 transition-all flex items-center gap-1">
                                            <i class="fa-solid fa-xmark"></i>
                                            <span>Reject</span>
                                        </button>
                                    @endif

                                    <form action="{{ route('platform.venues.toggle', $v->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" title="Toggle active status"
                                                class="px-2.5 py-1.5 rounded-xl bg-stone-200 dark:bg-stone-800 text-theme-heading hover:bg-stone-300 text-xs font-bold border border-stone-300 dark:border-stone-700">
                                            <i class="fa-solid fa-power-off {{ $v->is_active ? 'text-emerald-500' : 'text-stone-400' }}"></i>
                                        </button>
                                    </form>

                                    <a href="{{ route('venues.show', $v->slug) }}" target="_blank" title="View booking page"
                                       class="px-2.5 py-1.5 rounded-xl bg-cyan-500/10 text-cyan-600 hover:bg-cyan-500 hover:text-slate-950 text-xs font-bold transition-all">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-theme-muted">
                                No venues found for this filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($venues->hasPages())
            <div class="p-4 border-t border-stone-200 dark:border-stone-800">
                {{ $venues->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="rounded-3xl glass-card border border-stone-200 dark:border-stone-800 max-w-md w-full p-6 space-y-4 shadow-2xl">
        <h3 class="text-base font-black text-theme-heading flex items-center gap-2">
            <i class="fa-solid fa-triangle-exclamation text-rose-500"></i>
            <span>Reject Venue Application</span>
        </h3>
        <p class="text-xs text-theme-muted" id="rejectModalSubtext">
            Please provide a reason for declining this venue.
        </p>

        <form action="" method="POST" id="rejectVenueForm" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-theme-heading mb-1">Reason for Rejection</label>
                <textarea name="reason" rows="3" required
                          placeholder="e.g. Incomplete address, invalid contact details, or duplicate venue listing."
                          class="w-full p-3 rounded-xl bg-stone-100 dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-xs font-medium text-theme-heading focus:ring-2 focus:ring-rose-500 focus:outline-none"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="closeRejectModal()" class="px-4 py-2 rounded-xl bg-stone-200 dark:bg-stone-800 text-xs font-bold text-theme-heading">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-rose-500 hover:bg-rose-400 text-cascading-white text-xs font-bold shadow-md">
                    Confirm Rejection
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openRejectModal(venueId, venueName) {
        document.getElementById('rejectVenueForm').action = `/platform/venues/${venueId}/reject`;
        document.getElementById('rejectModalSubtext').textContent = `Declining venue '${venueName}'. This will notify the court owner.`;
        document.getElementById('rejectModal').classList.remove('hidden');
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').classList.add('hidden');
    }
</script>
@endpush
@endsection
