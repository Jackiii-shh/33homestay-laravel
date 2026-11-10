@extends('layouts.app')
@section('title', 'Dashboard Pengelola')
@section('heading', 'Dashboard Pengelola')

@section('content')
@php
    $card = 'rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/80';
    $btn = 'rounded-lg px-3 py-1.5 text-xs font-bold transition';
    $departures = $inHouse->filter(fn ($r) => $r->check_out->lte(today()));
    $stayers = $inHouse->reject(fn ($r) => $r->check_out->lte(today()));
@endphp

<div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-5">
    @foreach ([
        ['Tiba hari ini', $stats['arrivals_today'], 'plane-landing', 'text-sky-700', 'bg-sky-50 text-sky-600'],
        ['Sedang menginap', $stats['in_house'], 'bed-double', 'text-emerald-700', 'bg-emerald-50 text-emerald-600'],
        ['Check-out hari ini', $stats['departures_today'], 'plane-takeoff', 'text-slate-700', 'bg-slate-100 text-slate-600'],
        ['Menunggu DP', $stats['pending'], 'clock', 'text-amber-700', 'bg-amber-50 text-amber-600'],
        ['Kamar terpakai', $stats['occupied'].' / '.$stats['total_rooms'], 'bar-chart-3', 'text-forest-800', 'bg-forest-50 text-forest-700'],
    ] as [$label, $value, $icon, $color, $iconBg])
        <div class="{{ $card }}">
            <div class="flex items-center justify-between">
                <span class="text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-slate-400">{{ $label }}</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg {{ $iconBg }}">
                    <x-icon name="{{ $icon }}" class="h-4 w-4" />
                </div>
            </div>
            <p class="mt-2 text-2xl sm:text-3xl font-extrabold {{ $color }}">{{ $value }}</p>
        </div>
    @endforeach
</div>

<div class="mt-6 flex flex-wrap gap-2.5 sm:gap-3">
    <a href="{{ route('reservations.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-forest-800 px-4 py-2.5 text-xs sm:text-sm font-bold text-white shadow transition hover:bg-forest-900">
        <x-icon name="plus-circle" class="h-4 w-4" />
        <span>Reservasi Baru</span>
    </a>
    <a href="{{ route('calendar') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-4 py-2.5 text-xs sm:text-sm font-bold text-slate-700 shadow-sm ring-1 ring-slate-200 hover:bg-slate-50 transition">
        <x-icon name="calendar-days" class="h-4 w-4 text-forest-700" />
        <span>Kalender</span>
    </a>
    <a href="{{ route('rooms.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-4 py-2.5 text-xs sm:text-sm font-bold text-slate-700 shadow-sm ring-1 ring-slate-200 hover:bg-slate-50 transition">
        <x-icon name="door-open" class="h-4 w-4 text-forest-700" />
        <span>Status Kamar</span>
    </a>
</div>

<div class="mt-6 grid gap-6 xl:grid-cols-3">
    <div class="space-y-6 xl:col-span-2">
        {{-- Kedatangan --}}
        <section class="{{ $card }}">
            <h2 class="flex items-center gap-2 text-base font-bold text-slate-900">
                <x-icon name="plane-landing" class="h-4 w-4 text-sky-600" />
                <span>Kedatangan hari ini & belum check-in</span>
            </h2>
            <div class="mt-3 divide-y divide-slate-100">
                @forelse ($arrivals as $r)
                    @php $late = $r->check_in->lt(today()); $cutoff = $r->checkInCutoff()->isPast(); @endphp
                    <div class="flex flex-wrap items-center justify-between gap-3 py-3">
                        <div>
                            <a href="{{ route('reservations.show', $r) }}" class="font-bold text-slate-900 hover:text-forest-700">{{ $r->guest_name }}</a>
                            <p class="text-xs text-slate-500">{{ $r->code }} · {{ $r->room->code }} · {{ $r->check_in->translatedFormat('d M') }} → {{ $r->check_out->translatedFormat('d M') }}
                                @if ($late)<span class="font-bold text-rose-600">· terlambat</span>@endif</p>
                            <p class="mt-1 text-xs">
                                <span class="rounded-full px-2 py-0.5 font-semibold ring-1 {{ $r->paymentStatusClasses() }}">{{ $r->paymentStatusLabel() }}</span>
                                @if ($r->balance > 0)<span class="ml-1 font-semibold text-rose-600">sisa {{ rupiah($r->balance) }}</span>@endif
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            @if ($r->balance > 0)
                                <a href="{{ route('reservations.show', $r) }}#pembayaran" class="{{ $btn }} bg-amber-100 text-amber-800 hover:bg-amber-200">Catat Pelunasan</a>
                            @else
                                <form method="POST" action="{{ route('reservations.check-in', $r) }}">@csrf
                                    <button class="{{ $btn }} bg-emerald-600 text-white hover:bg-emerald-700">Check-in</button>
                                </form>
                            @endif
                            @if ($cutoff)
                                <form method="POST" action="{{ route('reservations.no-show', $r) }}" onsubmit="return confirm(@js('Tandai '.$r->guest_name.' sebagai NO-SHOW? DP dinyatakan hangus dan kamar dibuka kembali.'))">@csrf
                                    <button class="{{ $btn }} bg-orange-100 text-orange-800 hover:bg-orange-200">No-show</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-xs sm:text-sm text-slate-400">Tidak ada kedatangan yang menunggu check-in.</p>
                @endforelse
            </div>
        </section>

        {{-- Check-out --}}
        <section class="{{ $card }}">
            <h2 class="flex items-center gap-2 text-base font-bold text-slate-900">
                <x-icon name="plane-takeoff" class="h-4 w-4 text-slate-500" />
                <span>Check-out hari ini</span>
            </h2>
            <div class="mt-3 divide-y divide-slate-100">
                @forelse ($departures as $r)
                    <div class="flex flex-wrap items-center justify-between gap-3 py-3">
                        <div>
                            <a href="{{ route('reservations.show', $r) }}" class="font-bold text-slate-900 hover:text-forest-700">{{ $r->guest_name }}</a>
                            <p class="text-xs text-slate-500">{{ $r->code }} · {{ $r->room->code }} · rencana keluar {{ $r->check_out->translatedFormat('d M') }}</p>
                        </div>
                        @if ($r->balance > 0)
                            <a href="{{ route('reservations.show', $r) }}#pembayaran" class="{{ $btn }} bg-amber-100 text-amber-800 hover:bg-amber-200">Selesaikan Pembayaran ({{ rupiah($r->balance) }})</a>
                        @else
                            <form method="POST" action="{{ route('reservations.check-out', $r) }}">@csrf
                                <button class="{{ $btn }} bg-slate-800 text-white hover:bg-slate-900">Check-out</button>
                            </form>
                        @endif
                    </div>
                @empty
                    <p class="py-6 text-center text-xs sm:text-sm text-slate-400">Tidak ada check-out yang menunggu.</p>
                @endforelse
            </div>
            @if ($stayers->isNotEmpty())
                <p class="mt-3 border-t border-slate-100 pt-3 text-xs text-slate-500">Masih menginap: {{ $stayers->map(fn ($r) => $r->guest_name.' ('.$r->room->code.', s.d. '.$r->check_out->translatedFormat('d M').')')->implode(' · ') }}</p>
            @endif
        </section>

        {{-- Menunggu DP --}}
        <section class="{{ $card }}">
            <h2 class="flex items-center gap-2 text-base font-bold text-slate-900">
                <x-icon name="clock" class="h-4 w-4 text-amber-600" />
                <span>Menunggu DP</span>
            </h2>
            <div class="mt-3 divide-y divide-slate-100">
                @forelse ($pending as $r)
                    <div class="flex flex-wrap items-center justify-between gap-3 py-3">
                        <div>
                            <a href="{{ route('reservations.show', $r) }}" class="font-bold text-slate-900 hover:text-forest-700">{{ $r->guest_name }}</a>
                            <p class="text-xs text-slate-500">{{ $r->code }} · {{ $r->room->code }} · {{ $r->check_in->translatedFormat('d M') }} → {{ $r->check_out->translatedFormat('d M') }} · DP min {{ rupiah($r->minimumDp()) }}</p>
                        </div>
                        <div class="text-right text-xs">
                            @if ($r->dp_deadline)
                                <p class="{{ $r->dp_deadline->isPast() ? 'text-rose-600 font-bold' : 'text-amber-700 font-semibold' }}">Batas: {{ $r->dp_deadline->translatedFormat('d M H:i') }}</p>
                                <p class="text-[11px] text-slate-400">{{ $r->dp_deadline->diffForHumans() }}</p>
                            @endif
                            <a href="{{ route('reservations.show', $r) }}#pembayaran" class="mt-1 inline-block {{ $btn }} bg-forest-800 text-white hover:bg-forest-900">Catat DP</a>
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-xs sm:text-sm text-slate-400">Tidak ada reservasi yang menunggu DP.</p>
                @endforelse
            </div>
        </section>
    </div>

    <div class="space-y-6">
        {{-- Status kamar --}}
        <section class="{{ $card }}">
            <h2 class="flex items-center gap-2 text-base font-bold text-slate-900">
                <x-icon name="door-open" class="h-4 w-4 text-forest-700" />
                <span>Status Kamar Hari Ini</span>
            </h2>
            <div class="mt-3 grid grid-cols-2 gap-2">
                @foreach ($rooms as $row)
                    <div class="rounded-xl p-3 text-xs {{ $row['color'] }}">
                        <p class="text-sm font-bold">{{ $row['room']->code }}</p>
                        <p class="font-semibold">{{ $row['label'] }}</p>
                        @if ($row['reservation'])<p class="truncate opacity-80">{{ $row['reservation']->guest_name }}</p>@endif
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Checklist OTA --}}
        <section class="{{ $card }}">
            <h2 class="flex items-center gap-2 text-base font-bold text-slate-900">
                <x-icon name="globe" class="h-4 w-4 text-slate-500" />
                <span>Checklist OTA (Manual)</span>
            </h2>
            <p class="mt-1 text-xs text-slate-500">Kamar yang dipesan harus diblokir di Agoda &amp; Tiket.com agar tidak terjadi double booking.</p>

            <p class="mt-4 text-[11px] font-bold uppercase tracking-wider text-rose-600">Perlu DIBLOKIR ({{ $otaBlock->count() }})</p>
            <div class="mt-2 space-y-2">
                @forelse ($otaBlock as $r)
                    <div class="flex items-center justify-between gap-2 rounded-xl bg-rose-50 p-3 text-xs">
                        <div>
                            <a href="{{ route('reservations.show', $r) }}" class="font-bold text-slate-900">{{ $r->room->code }} · {{ $r->check_in->translatedFormat('d M') }}–{{ $r->check_out->translatedFormat('d M') }}</a>
                            <p class="text-slate-500">{{ $r->guest_name }}</p>
                        </div>
                        <form method="POST" action="{{ route('reservations.ota-block', $r) }}">@csrf
                            <button class="{{ $btn }} bg-rose-600 text-white hover:bg-rose-700">Sudah diblokir</button>
                        </form>
                    </div>
                @empty
                    <p class="text-xs text-slate-400">Semua tanggal sudah diblokir ✓</p>
                @endforelse
            </div>

            <p class="mt-4 text-[11px] font-bold uppercase tracking-wider text-amber-600">Perlu DIBUKA kembali ({{ $otaUnblock->count() }})</p>
            <div class="mt-2 space-y-2">
                @forelse ($otaUnblock as $r)
                    <div class="flex items-center justify-between gap-2 rounded-xl bg-amber-50 p-3 text-xs">
                        <div>
                            <a href="{{ route('reservations.show', $r) }}" class="font-bold text-slate-900">{{ $r->room->code }} · {{ $r->check_in->translatedFormat('d M') }}–{{ $r->check_out->translatedFormat('d M') }}</a>
                            <p class="text-slate-500">{{ $r->statusLabel() }} · {{ $r->guest_name }}</p>
                        </div>
                        <form method="POST" action="{{ route('reservations.ota-unblock', $r) }}">@csrf
                            <button class="{{ $btn }} bg-amber-500 text-white hover:bg-amber-600">Sudah dibuka</button>
                        </form>
                    </div>
                @empty
                    <p class="text-xs text-slate-400">Tidak ada tanggal yang perlu dibuka ✓</p>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
