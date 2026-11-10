@extends('layouts.app')
@section('title', 'Laporan Pembayaran')
@section('heading', 'Laporan Pembayaran')

@section('content')
@php $inp = 'rounded-xl border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500'; $card = 'rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200'; @endphp

<form method="GET" class="grid gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:grid-cols-2 lg:grid-cols-5">
    <label class="text-xs font-semibold text-slate-500">Dari<input type="date" name="from" value="{{ $from->toDateString() }}" class="{{ $inp }} mt-1 w-full"></label>
    <label class="text-xs font-semibold text-slate-500">Sampai<input type="date" name="to" value="{{ $to->toDateString() }}" class="{{ $inp }} mt-1 w-full"></label>
    <label class="text-xs font-semibold text-slate-500">Metode
        <select name="method" class="{{ $inp }} mt-1 w-full"><option value="">Semua</option>
            @foreach (\App\Models\Payment::METHODS as $k => $l)<option value="{{ $k }}" @selected(request('method') === $k)>{{ $l }}</option>@endforeach
        </select></label>
    <label class="text-xs font-semibold text-slate-500">Dicatat oleh
        <select name="user" class="{{ $inp }} mt-1 w-full"><option value="">Semua</option>
            @foreach ($staff as $u)<option value="{{ $u->id }}" @selected((string) request('user') === (string) $u->id)>{{ $u->name }}</option>@endforeach
        </select></label>
    <div class="flex items-end gap-2">
        <button class="rounded-xl bg-forest-800 px-5 py-2 text-sm font-bold text-white hover:bg-forest-900 transition">Terapkan</button>
        <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="inline-flex items-center gap-1.5 rounded-xl bg-white px-4 py-2 text-sm font-bold text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50 transition">
            <x-icon name="clipboard-list" class="h-4 w-4 text-slate-400" />
            <span>Ekspor CSV</span>
        </a>
    </div>
</form>

<div class="mt-5 grid gap-4 lg:grid-cols-3">
    <div class="{{ $card }}"><p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Total diterima</p><p class="mt-2 text-3xl font-extrabold text-emerald-700">{{ rupiah($total) }}</p><p class="mt-1 text-xs text-slate-400">{{ $from->translatedFormat('d M Y') }} – {{ $to->translatedFormat('d M Y') }}</p></div>
    <div class="{{ $card }}"><p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Per metode</p>
        <ul class="mt-2 space-y-1 text-sm">@forelse ($byMethod as $m)<li class="flex justify-between"><span>{{ \App\Models\Payment::METHODS[$m->method] ?? $m->method }} <span class="text-slate-400">({{ $m->n }}×)</span></span><span class="font-bold">{{ rupiah($m->total) }}</span></li>@empty<li class="text-slate-400">—</li>@endforelse</ul></div>
    <div class="{{ $card }}"><p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Per pencatat (pengendalian internal)</p>
        <ul class="mt-2 space-y-1 text-sm">@forelse ($byUser as $u)<li class="flex justify-between"><span>{{ $u->user_name }} <span class="text-slate-400">({{ $u->n }}×)</span></span><span class="font-bold">{{ rupiah($u->total) }}</span></li>@empty<li class="text-slate-400">—</li>@endforelse</ul></div>
</div>

<div class="mt-5 overflow-x-auto rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
            <tr><th class="px-4 py-3">Waktu</th><th class="px-4 py-3">Invoice</th><th class="px-4 py-3">Reservasi</th><th class="px-4 py-3">Jenis</th><th class="px-4 py-3">Metode</th><th class="px-4 py-3 text-right">Nominal</th><th class="px-4 py-3">Dicatat oleh</th></tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($payments as $p)
                <tr class="hover:bg-slate-50">
                    <td class="whitespace-nowrap px-4 py-3">{{ $p->paid_at->translatedFormat('d M Y H:i') }}</td>
                    <td class="px-4 py-3 font-mono text-xs">@if ($p->invoice)<a href="{{ route('invoices.pdf', $p->invoice) }}" class="text-brand-700 hover:underline">{{ $p->invoice->number }}</a>@endif</td>
                    <td class="px-4 py-3"><a href="{{ route('reservations.show', $p->reservation) }}" class="font-semibold text-slate-800 hover:text-brand-700">{{ $p->reservation->code }}</a><p class="text-xs text-slate-400">{{ $p->reservation->guest_name }} · {{ $p->reservation->room->code }}</p></td>
                    <td class="px-4 py-3">{{ $p->typeLabel() }}</td>
                    <td class="px-4 py-3">{{ $p->methodLabel() }}</td>
                    <td class="whitespace-nowrap px-4 py-3 text-right font-bold">{{ rupiah($p->amount) }}</td>
                    <td class="px-4 py-3 text-xs text-slate-500">{{ $p->receiver?->name ?? 'Sistem' }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-10 text-center text-slate-400">Tidak ada pembayaran pada rentang ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $payments->links() }}</div>
@endsection
