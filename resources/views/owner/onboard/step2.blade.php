@extends('owner.onboard.layout', ['step' => 2])

@section('onboard-step')
<form action="{{ route('owner.onboard.post_step2') }}" method="POST" class="space-y-6">
    @csrf

    <div class="border-b border-stone-200 dark:border-stone-800 pb-4">
        <h2 class="text-xl font-black text-theme-heading flex items-center gap-2.5">
            <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-500 flex items-center justify-center text-sm font-black">2</span>
            Location Pin & Geocoordinates
        </h2>
        <p class="text-xs text-theme-muted mt-1">Set your exact coordinates. Players browse courts sorted by proximity to their current location using the Haversine algorithm.</p>
    </div>

    <!-- Quick Location Presets & GPS Auto-detect -->
    <div class="p-4 rounded-2xl bg-emerald-500/5 border border-emerald-500/20 space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-xs font-bold text-emerald-500 dark:text-emerald-400 flex items-center gap-2">
                    <i class="fa-solid fa-location-crosshairs"></i> Fast Geolocation Helper
                </h3>
                <p class="text-[11px] text-theme-muted">Click to auto-fill latitude and longitude via device GPS or city preset.</p>
            </div>
            <button type="button" onclick="detectGPSLocation()" id="gpsBtn"
                class="px-3.5 py-2 rounded-xl bg-emerald-500 text-slate-950 font-black text-xs shadow-md shadow-emerald-500/20 hover:bg-emerald-400 transition-all flex items-center gap-2 shrink-0 cursor-pointer">
                <i class="fa-solid fa-crosshairs"></i>
                <span>Use My GPS Location</span>
            </button>
        </div>

        <div class="pt-2 border-t border-emerald-500/10 flex flex-wrap gap-2 text-[11px]">
            <span class="text-theme-muted font-bold self-center mr-1">Quick Presets:</span>
            <button type="button" onclick="setCoordinates(15.5833, 120.9167, 'Talavera, Nueva Ecija')" class="px-2.5 py-1 rounded-lg bg-stone-200 dark:bg-stone-800 hover:border-emerald-500 border border-transparent text-theme-heading font-medium transition-colors">Talavera</button>
            <button type="button" onclick="setCoordinates(15.4859, 120.9673, 'Cabanatuan City')" class="px-2.5 py-1 rounded-lg bg-stone-200 dark:bg-stone-800 hover:border-emerald-500 border border-transparent text-theme-heading font-medium transition-colors">Cabanatuan</button>
            <button type="button" onclick="setCoordinates(14.6760, 121.0437, 'Quezon City')" class="px-2.5 py-1 rounded-lg bg-stone-200 dark:bg-stone-800 hover:border-emerald-500 border border-transparent text-theme-heading font-medium transition-colors">Quezon City</button>
            <button type="button" onclick="setCoordinates(14.5547, 121.0244, 'Makati / BGC')" class="px-2.5 py-1 rounded-lg bg-stone-200 dark:bg-stone-800 hover:border-emerald-500 border border-transparent text-theme-heading font-medium transition-colors">Makati/BGC</button>
            <button type="button" onclick="setCoordinates(15.1450, 120.5887, 'Angeles / Clark')" class="px-2.5 py-1 rounded-lg bg-stone-200 dark:bg-stone-800 hover:border-emerald-500 border border-transparent text-theme-heading font-medium transition-colors">Angeles/Clark</button>
            <button type="button" onclick="setCoordinates(10.3157, 123.8854, 'Cebu City')" class="px-2.5 py-1 rounded-lg bg-stone-200 dark:bg-stone-800 hover:border-emerald-500 border border-transparent text-theme-heading font-medium transition-colors">Cebu City</button>
        </div>
    </div>

    <!-- Coordinate Inputs -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 text-xs">
        <div>
            <label class="block font-bold text-theme-body mb-1">Latitude (Decimal Degrees) <span class="text-rose-500">*</span></label>
            <div class="relative">
                <input type="number" step="any" name="latitude" id="latInput"
                    value="{{ old('latitude', $onboarding['step2']['latitude'] ?? '15.5833000') }}" required
                    placeholder="e.g. 15.5833"
                    class="w-full px-4 py-3 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading font-mono font-bold focus:outline-none focus:border-emerald-500">
                <span class="absolute right-3 top-3 text-stone-400 font-bold">° N</span>
            </div>
            @error('latitude') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block font-bold text-theme-body mb-1">Longitude (Decimal Degrees) <span class="text-rose-500">*</span></label>
            <div class="relative">
                <input type="number" step="any" name="longitude" id="lngInput"
                    value="{{ old('longitude', $onboarding['step2']['longitude'] ?? '120.9167000') }}" required
                    placeholder="e.g. 120.9167"
                    class="w-full px-4 py-3 rounded-xl bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-theme-heading font-mono font-bold focus:outline-none focus:border-emerald-500">
                <span class="absolute right-3 top-3 text-stone-400 font-bold">° E</span>
            </div>
            @error('longitude') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
        </div>
    </div>

    <!-- Interactive Visual Pin Box -->
    <div class="p-6 rounded-2xl bg-stone-100 dark:bg-stone-900/60 border border-stone-200 dark:border-stone-800 text-center space-y-3">
        <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-500 flex items-center justify-center text-xl mx-auto shadow-inner">
            <i class="fa-solid fa-map-pin animate-bounce"></i>
        </div>
        <div>
            <div class="text-xs font-bold text-theme-heading" id="locationLabel">Selected Pin Coordinates</div>
            <div class="text-[11px] text-theme-muted font-mono mt-0.5">
                Lat: <span id="previewLat" class="text-emerald-500 font-bold">15.5833000</span>, 
                Lng: <span id="previewLng" class="text-emerald-500 font-bold">120.9167000</span>
            </div>
        </div>
        <p class="text-[11px] text-theme-muted max-w-md mx-auto">
            These coordinates will place your venue pin on the public platform discovery map. Nearby players within 50km will see your venue first.
        </p>
    </div>

    <div class="pt-6 border-t border-stone-200 dark:border-stone-800 flex items-center justify-between">
        <a href="{{ route('owner.onboard.step1') }}" class="px-5 py-2.5 rounded-xl border border-stone-300 dark:border-stone-700 text-theme-muted font-bold text-xs hover:bg-stone-100 dark:hover:bg-stone-800 transition-colors flex items-center gap-2">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back</span>
        </a>
        <button type="submit" class="px-6 py-3 rounded-xl bg-gradient-to-r from-emerald-500 to-cyan-500 hover:from-emerald-400 hover:to-cyan-400 text-slate-950 font-black text-xs shadow-lg shadow-emerald-500/20 flex items-center gap-2 transition-transform hover:scale-[1.02]">
            <span>Next: Add First Court</span>
            <i class="fa-solid fa-arrow-right"></i>
        </button>
    </div>
</form>

<script>
    function updatePreview() {
        const lat = document.getElementById('latInput').value;
        const lng = document.getElementById('lngInput').value;
        document.getElementById('previewLat').innerText = lat || '0.0000';
        document.getElementById('previewLng').innerText = lng || '0.0000';
    }

    document.getElementById('latInput').addEventListener('input', updatePreview);
    document.getElementById('lngInput').addEventListener('input', updatePreview);

    function setCoordinates(lat, lng, label) {
        document.getElementById('latInput').value = lat.toFixed(7);
        document.getElementById('lngInput').value = lng.toFixed(7);
        document.getElementById('locationLabel').innerText = 'Preset: ' + label;
        updatePreview();
    }

    function detectGPSLocation() {
        const btn = document.getElementById('gpsBtn');
        if (!navigator.geolocation) {
            alert('Geolocation is not supported by your browser.');
            return;
        }

        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Detecting...';
        btn.disabled = true;

        navigator.geolocation.getCurrentPosition(
            (pos) => {
                setCoordinates(pos.coords.latitude, pos.coords.longitude, 'Detected via Browser GPS');
                btn.innerHTML = '<i class="fa-solid fa-check"></i> GPS Detected!';
                btn.disabled = false;
            },
            (err) => {
                alert('Could not retrieve GPS location: ' + err.message);
                btn.innerHTML = '<i class="fa-solid fa-crosshairs"></i> Use My GPS Location';
                btn.disabled = false;
            },
            { enableHighAccuracy: true, timeout: 8000 }
        );
    }

    updatePreview();
</script>
@endsection
