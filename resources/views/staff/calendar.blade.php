@extends('layouts.app')
@section('title', 'Kalender Ketersediaan')
@section('heading', 'Kalender Ketersediaan')

@section('content')
@php
    $isStaff = auth()->user()->isPengelola();
    $colors = [
        'pending_dp' => 'bg-amber-200 text-amber-900 hover:bg-amber-300',
        'confirmed' => 'bg-sky-200 text-sky-900 hover:bg-sky-300',
        'checked_in' => 'bg-emerald-300 text-emerald-950 hover:bg-emerald-400',
    ];
@endphp

<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <div class="flex items-center gap-2">
        <a href="{{ route('calendar', ['bulan' => $prev]) }}" class="rounded-lg bg-white px-3 py-2 text-sm font-bold ring-1 ring-slate-200 hover:bg-slate-50">←</a>
        <span class="min-w-[9rem] text-center text-lg font-extrabold text-slate-900">{{ $month->translatedFormat('F Y') }}</span>
        <a href="{{ route('calendar', ['bulan' => $next]) }}" class="rounded-lg bg-white px-3 py-2 text-sm font-bold ring-1 ring-slate-200 hover:bg-slate-50">→</a>
        <a href="{{ route('calendar') }}" class="ml-2 rounded-lg bg-brand-50 px-3 py-2 text-sm font-bold text-brand-700 hover:bg-brand-100">Bulan ini</a>
    </div>
    <div class="flex flex-wrap items-center gap-3 text-xs font-semibold">
        <span class="flex items-center gap-1"><i class="inline-block h-3 w-3 rounded bg-amber-200"></i> Menunggu DP</span>
        <span class="flex items-center gap-1"><i class="inline-block h-3 w-3 rounded bg-sky-200"></i> Terkonfirmasi</span>
        <span class="flex items-center gap-1"><i class="inline-block h-3 w-3 rounded bg-emerald-300"></i> Check-in</span>
        <span class="flex items-center gap-1"><i class="inline-block h-3 w-3 rounded bg-white ring-1 ring-slate-300"></i> Kosong</span>
    </div>
</div>

<div class="overflow-x-auto rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
    <table class="border-collapse text-xs" style="min-width: {{ 120 + $days * 44 }}px">
        <thead>
            <tr>
                <th class="sticky left-0 z-10 w-[120px] border-b border-r border-slate-200 bg-slate-50 px-3 py-2 text-left">Unit</th>
                @for ($d = 1; $d <= $days; $d++)
                    @php $date = $month->copy()->day($d); $isToday = $date->isToday(); $weekend = $date->isWeekend() || $date->dayOfWeek === \Illuminate\Support\Carbon::FRIDAY; @endphp
                    <th class="w-[44px] border-b border-r border-slate-100 px-1 py-1.5 text-center {{ $isToday ? 'bg-brand-600 text-white' : ($weekend ? 'bg-rose-50 text-rose-700' : 'bg-slate-50 text-slate-500') }}">
                        <div class="font-bold">{{ $d }}</div>
                        <div class="text-[10px] font-medium uppercase">{{ $date->translatedFormat('D') }}</div>
                    </th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <th class="sticky left-0 z-10 border-b border-r border-slate-200 bg-white px-3 py-2 text-left">
                        <span class="block text-sm font-bold text-slate-800">{{ $row['room']->code }}</span>
                        <span class="block text-[10px] font-medium text-slate-400">{{ $row['room']->type->name }}</span>
                        @if ($row['room']->status === \App\Models\Room::STATUS_MAINTENANCE)<span class="text-[10px] font-bold text-slate-500">MAINTENANCE</span>@endif
                    </th>
                    @foreach ($row['segments'] as $seg)
                        @if ($seg['res'])
                            @php $res = $seg['res']; @endphp
                            <td colspan="{{ $seg['span'] }}" class="h-12 border-b border-r border-white p-0.5">
                                <a href="{{ route('reservations.show', $res) }}" title="{{ $res->code }} · {{ $res->guest_name }} · {{ $res->check_in->format('d/m') }}–{{ $res->check_out->format('d/m') }} · {{ $res->statusLabel() }}"
                                   class="flex h-full items-center overflow-hidden rounded-md px-2 font-semibold {{ $colors[$res->status] ?? 'bg-slate-200' }}">
                                    <span class="truncate">{{ $res->guest_name }}</span>
                                </a>
                            </td>
                        @else
                            @php $date = $seg['date']; @endphp
                            <td class="h-12 border-b border-r border-slate-100 p-0 text-center {{ $date->isToday() ? 'bg-brand-50' : '' }} {{ $row['room']->status === \App\Models\Room::STATUS_MAINTENANCE ? 'bg-slate-100' : '' }}">
                                @if ($isStaff && $date->gte(today()) && $row['room']->status !== \App\Models\Room::STATUS_MAINTENANCE)
                                    <a href="{{ route('reservations.create', ['room_id' => $row['room']->id, 'check_in' => $date->toDateString(), 'check_out' => $date->copy()->addDay()->toDateString()]) }}"
                                       class="block h-full w-full leading-[3rem] text-transparent hover:bg-brand-100 hover:text-brand-700" title="Buat reservasi {{ $row['room']->code }} · {{ $date->translatedFormat('d M') }}">+</a>
                                @endif
                            </td>
                        @endif
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<p class="mt-3 text-xs text-slate-500">Setiap kolom mewakili satu malam. Tamu yang check-out pada tanggal X dan tamu lain yang check-in pada tanggal X tidak dianggap bentrok. @if ($isStaff)Klik sel kosong (+) untuk membuat reservasi pada tanggal tersebut.@endif</p>
@endsection
