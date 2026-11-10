@if (session('success'))
    <div class="mb-4 flex items-center gap-2.5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs sm:text-sm text-emerald-800">
        <x-icon name="check-circle" class="h-4 w-4 flex-shrink-0 text-emerald-600" />
        <span>{{ session('success') }}</span>
    </div>
@endif

@if (session('error'))
    <div class="mb-4 flex items-center gap-2.5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs sm:text-sm text-rose-800">
        <x-icon name="alert-circle" class="h-4 w-4 flex-shrink-0 text-rose-600" />
        <span>{{ session('error') }}</span>
    </div>
@endif

@if ($errors->any() && ! ($hideErrors ?? false))
    <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs sm:text-sm text-rose-800">
        <p class="flex items-center gap-2 font-bold">
            <x-icon name="alert-triangle" class="h-4 w-4 text-rose-600 flex-shrink-0" />
            <span>Terjadi kesalahan:</span>
        </p>
        <ul class="ml-6 mt-1 list-disc space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if ($alt = session('alternatives'))
    <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-4 text-xs sm:text-sm text-amber-900">
        <p class="flex items-center gap-2 font-bold">
            <x-icon name="shield-alert" class="h-4 w-4 text-amber-700 flex-shrink-0" />
            <span>Double booking dicegah. Alternatif yang tersedia untuk {{ \Illuminate\Support\Carbon::parse($alt['check_in'])->translatedFormat('d M') }} – {{ \Illuminate\Support\Carbon::parse($alt['check_out'])->translatedFormat('d M Y') }}:</span>
        </p>
        <ul class="ml-6 mt-2 list-disc space-y-1">
            @if (count($alt['same_type']))
                <li>Unit lain tipe sama: <strong>{{ collect($alt['same_type'])->pluck('label')->implode(', ') }}</strong></li>
            @endif
            @if (count($alt['other_type']))
                <li>Tipe lain yang kosong: <strong>{{ collect($alt['other_type'])->pluck('label')->implode(', ') }}</strong></li>
            @endif
            @foreach ($alt['windows'] as $w)
                <li>Tanggal terdekat: <strong>{{ \Illuminate\Support\Carbon::parse($w['check_in'])->translatedFormat('d M') }} – {{ \Illuminate\Support\Carbon::parse($w['check_out'])->translatedFormat('d M') }}</strong> ({{ $w['units'] }} unit kosong)</li>
            @endforeach
            @if (! count($alt['same_type']) && ! count($alt['other_type']) && ! count($alt['windows']))
                <li>Tidak ada alternatif dekat. Tawarkan penginapan rekanan kepada tamu.</li>
            @endif
        </ul>
        <a href="{{ route('reservations.show', $alt['conflict']['id']) }}" class="mt-2 inline-block font-bold text-amber-900 underline">Lihat reservasi {{ $alt['conflict']['code'] }} yang menyebabkan bentrok</a>
    </div>
@endif
