@extends('layouts.app')
@section('title', 'Dashboard Owner')
@section('heading', 'Dashboard Owner')

@section('content')
@php
    $card = 'rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/80';
    $outstandingTotal = $outstanding->sum(fn ($r) => $r->balance);
    $totalRes = $statusCounts->sum();
@endphp

<div class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div class="flex items-center gap-2">
        <a href="{{ route('owner.dashboard', ['bulan' => $prev]) }}" class="grid h-9 w-9 place-items-center rounded-xl bg-white text-sm font-bold shadow-sm ring-1 ring-slate-200 hover:bg-slate-50">
            <x-icon name="arrow-left" class="h-4 w-4" />
        </a>
        <span class="min-w-[9rem] text-center text-base sm:text-lg font-bold text-slate-900">{{ $month->translatedFormat('F Y') }}</span>
        <a href="{{ route('owner.dashboard', ['bulan' => $next]) }}" class="grid h-9 w-9 place-items-center rounded-xl bg-white text-sm font-bold shadow-sm ring-1 ring-slate-200 hover:bg-slate-50">
            <x-icon name="arrow-right" class="h-4 w-4" />
        </a>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('calendar') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-forest-800 px-4 py-2 text-xs sm:text-sm font-bold text-white shadow transition hover:bg-forest-900">
            <x-icon name="calendar-days" class="h-4 w-4" />
            <span>Kalender Real-time</span>
        </a>
        <a href="{{ route('owner.payments') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-4 py-2 text-xs sm:text-sm font-bold text-slate-700 shadow-sm ring-1 ring-slate-200 hover:bg-slate-50 transition">
            <x-icon name="credit-card" class="h-4 w-4 text-forest-700" />
            <span>Laporan Pembayaran</span>
        </a>
    </div>
</div>

{{-- KPI --}}
<div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
    <div class="{{ $card }}">
        <p class="text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-slate-400">Pendapatan diterima</p>
        <p class="mt-2 text-xl sm:text-2xl font-extrabold text-emerald-700">{{ rupiah($revenue) }}</p>
        <p class="mt-1 text-[11px] text-slate-400">Seluruh pembayaran bulan ini</p>
    </div>
    <div class="{{ $card }}">
        <p class="text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-slate-400">Okupansi</p>
        <p class="mt-2 text-xl sm:text-2xl font-extrabold text-forest-800">{{ $occupancy }}%</p>
        <p class="mt-1 text-[11px] text-slate-400">{{ $sold }} malam terjual · {{ $units }} unit</p>
    </div>
    <div class="{{ $card }} ring-rose-200 bg-rose-50/20">
        <p class="text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-rose-600">Double booking dicegah</p>
        <p class="mt-2 text-xl sm:text-2xl font-extrabold text-rose-600">{{ $blockedMonth }}</p>
        <p class="mt-1 text-[11px] text-slate-400">Bulan ini · total {{ $blockedTotal }} di sistem</p>
    </div>
    <div class="{{ $card }}">
        <p class="text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-slate-400">Piutang tamu</p>
        <p class="mt-2 text-xl sm:text-2xl font-extrabold {{ $outstandingTotal > 0 ? 'text-amber-600' : 'text-slate-700' }}">{{ rupiah($outstandingTotal) }}</p>
        <p class="mt-1 text-[11px] text-slate-400">{{ $outstanding->count() }} reservasi belum lunas</p>
    </div>
</div>

<div class="mt-4 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
    @foreach ([
        ['Reservasi bulan ini', $totalRes, 'text-slate-800'],
        ['Selesai / menginap', ($statusCounts['checked_out'] ?? 0) + ($statusCounts['checked_in'] ?? 0), 'text-emerald-700'],
        ['No-show', $statusCounts['no_show'] ?? 0, 'text-orange-600'],
        ['Dibatalkan', $statusCounts['cancelled'] ?? 0, 'text-rose-600'],
    ] as [$label, $value, $color])
        <div class="{{ $card }}">
            <p class="text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-slate-400">{{ $label }}</p>
            <p class="mt-2 text-xl sm:text-2xl font-extrabold {{ $color }}">{{ $value }}</p>
        </div>
    @endforeach
</div>

{{-- Grafik --}}
<div class="mt-6 grid gap-6 lg:grid-cols-3">
    <section class="{{ $card }} lg:col-span-2">
        <h2 class="text-base font-bold text-slate-900">Pendapatan harian — {{ $month->translatedFormat('F Y') }}</h2>
        <div class="mt-3 h-64"><canvas id="dailyChart"></canvas></div>
    </section>
    <section class="{{ $card }}">
        <h2 class="text-base font-bold text-slate-900">Pendapatan 6 bulan</h2>
        <div class="mt-3 h-64"><canvas id="monthlyChart"></canvas></div>
    </section>
</div>

<div class="mt-6 grid gap-6 lg:grid-cols-3">
    <section class="{{ $card }}">
        <h2 class="text-base font-bold text-slate-900">Okupansi per tipe</h2>
        <div class="mt-4 space-y-4">
            @foreach ($byType as $t)
                <div>
                    <div class="flex justify-between text-xs sm:text-sm">
                        <span class="font-semibold text-slate-700">{{ $t['name'] }}</span>
                        <span class="font-bold text-forest-800">{{ $t['occupancy'] }}%</span>
                    </div>
                    <div class="mt-1 h-2 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full bg-forest-700" style="width: {{ min(100, $t['occupancy']) }}%"></div>
                    </div>
                    <p class="mt-1 text-[11px] text-slate-400">{{ $t['sold'] }} malam terjual · {{ $t['units'] }} unit</p>
                </div>
            @endforeach
        </div>

        <h2 class="mt-8 text-base font-bold text-slate-900">Metode pembayaran</h2>
        <ul class="mt-3 space-y-2 text-xs sm:text-sm">
            @forelse ($revenueByMethod as $method => $total)
                <li class="flex justify-between">
                    <span class="text-slate-600">{{ \App\Models\Payment::METHODS[$method] ?? $method }}</span>
                    <span class="font-bold text-slate-900">{{ rupiah($total) }}</span>
                </li>
            @empty
                <li class="text-slate-400">Belum ada pembayaran bulan ini.</li>
            @endforelse
        </ul>

        <h2 class="mt-8 text-base font-bold text-slate-900">Kanal reservasi</h2>
        <ul class="mt-3 space-y-2 text-xs sm:text-sm">
            @forelse ($channelCounts as $channel => $count)
                <li class="flex justify-between">
                    <span class="text-slate-600">{{ \App\Models\Reservation::CHANNELS[$channel] ?? $channel }}</span>
                    <span class="font-bold text-slate-900">{{ $count }}</span>
                </li>
            @empty
                <li class="text-slate-400">Belum ada reservasi bulan ini.</li>
            @endforelse
        </ul>
    </section>

    <section class="{{ $card }}">
        <h2 class="text-base font-bold text-slate-900">Status kamar hari ini</h2>
        <div class="mt-3 grid grid-cols-2 gap-2">
            @foreach ($rooms as $row)
                <div class="rounded-xl p-3 text-xs {{ $row['color'] }}">
                    <p class="text-sm font-bold">{{ $row['room']->code }}</p>
                    <p class="font-semibold">{{ $row['label'] }}</p>
                    @if ($row['reservation'])<p class="truncate opacity-80">{{ $row['reservation']->guest_name }}</p>@endif
                </div>
            @endforeach
        </div>
        <p class="mt-3 text-[11px] text-slate-400">Data terpusat sama persis dengan yang dilihat pengelola.</p>
    </section>

    <section class="{{ $card }}">
        <h2 class="text-base font-bold text-slate-900">Kedatangan 7 hari ke depan</h2>
        <div class="mt-3 divide-y divide-slate-100">
            @forelse ($upcoming as $r)
                <a href="{{ route('reservations.show', $r) }}" class="block py-2.5 hover:bg-slate-50 transition rounded-lg px-1">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs sm:text-sm font-bold text-slate-800">{{ $r->guest_name }}</span>
                        <span class="rounded-full px-2 py-0.5 text-[10px] font-bold ring-1 {{ $r->statusClasses() }}">{{ $r->statusLabel() }}</span>
                    </div>
                    <p class="text-xs text-slate-500">{{ $r->check_in->translatedFormat('D, d M') }} · {{ $r->room->code }} · {{ $r->nights }} malam · {{ $r->paymentStatusLabel() }}</p>
                </a>
            @empty
                <p class="py-6 text-center text-xs sm:text-sm text-slate-400">Tidak ada kedatangan terjadwal.</p>
            @endforelse
        </div>
    </section>
</div>

<div class="mt-6 grid gap-6 lg:grid-cols-2">
    <section class="{{ $card }}">
        <h2 class="text-base font-bold text-slate-900">Piutang — sisa pembayaran tamu yang sudah tiba</h2>
        <div class="mt-3 divide-y divide-slate-100">
            @forelse ($outstanding as $r)
                <a href="{{ route('reservations.show', $r) }}" class="flex items-center justify-between gap-2 py-3 hover:bg-slate-50 transition rounded-lg px-1">
                    <div>
                        <p class="text-xs sm:text-sm font-bold text-slate-800">{{ $r->guest_name }}</p>
                        <p class="text-xs text-slate-500">{{ $r->code }} · {{ $r->room->code }} · {{ $r->statusLabel() }}</p>
                    </div>
                    <span class="text-xs sm:text-sm font-bold text-amber-600">{{ rupiah($r->balance) }}</span>
                </a>
            @empty
                <p class="py-6 text-center text-xs sm:text-sm text-slate-400">Tidak ada selisih pembayaran. ✓</p>
            @endforelse
        </div>
    </section>

    <section class="{{ $card }}">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-900">Aktivitas terbaru</h2>
            <a href="{{ route('owner.audit') }}" class="text-xs font-bold text-forest-800 hover:underline">Lihat semua</a>
        </div>
        <ul class="mt-3 divide-y divide-slate-100">
            @foreach ($recentLogs as $log)
                <li class="py-2.5 text-xs sm:text-sm">
                    <p class="font-semibold {{ $log->action === 'booking_conflict_blocked' ? 'text-rose-700' : 'text-slate-800' }}">{{ $log->actionLabel() }}</p>
                    <p class="line-clamp-2 text-xs text-slate-500">{{ $log->description }}</p>
                    <p class="text-[11px] text-slate-400">{{ $log->created_at->diffForHumans() }} · {{ $log->user?->name ?? 'Sistem' }}</p>
                </li>
            @endforeach
        </ul>
    </section>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
    const rp = v => 'Rp ' + new Intl.NumberFormat('id-ID').format(v);
    const axisRp = v => v >= 1000000 ? (v / 1000000) + ' jt' : (v >= 1000 ? (v / 1000) + ' rb' : v);
    new Chart(document.getElementById('dailyChart'), {
        type: 'bar',
        data: { labels: @json($chartLabels), datasets: [{ label: 'Pendapatan', data: @json($chartValues), backgroundColor: '#356535', borderRadius: 4 }] },
        options: { maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => rp(c.parsed.y) } } },
                   scales: { y: { beginAtZero: true, ticks: { callback: axisRp } }, x: { grid: { display: false } } } }
    });
    new Chart(document.getElementById('monthlyChart'), {
        type: 'line',
        data: { labels: @json(collect($monthly)->pluck('label')), datasets: [{ label: 'Pendapatan', data: @json(collect($monthly)->pluck('total')), borderColor: '#1f3520', backgroundColor: 'rgba(53,101,53,.15)', fill: true, tension: .3 }] },
        options: { maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => rp(c.parsed.y) } } },
                   scales: { y: { beginAtZero: true, ticks: { callback: axisRp } } } }
    });
</script>
@endpush
