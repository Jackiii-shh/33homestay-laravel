@extends('layouts.app')
@section('title', 'Daftar Reservasi')
@section('heading', 'Daftar Reservasi')

@section('content')
@php $isStaff = auth()->user()->isPengelola(); $inp = 'rounded-xl border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500'; @endphp

<form method="GET" class="grid gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:grid-cols-2 lg:grid-cols-6">
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari kode / nama / HP" class="{{ $inp }} lg:col-span-2">
    <select name="status" class="{{ $inp }}">
        <option value="">Semua status</option>
        @foreach (\App\Models\Reservation::STATUS_META as $key => [$label])
            <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
        @endforeach
    </select>
    <select name="channel" class="{{ $inp }}">
        <option value="">Semua kanal</option>
        @foreach (\App\Models\Reservation::CHANNELS as $key => $label)
            <option value="{{ $key }}" @selected(request('channel') === $key)>{{ $label }}</option>
        @endforeach
    </select>
    <input type="date" name="from" value="{{ request('from') }}" title="Menginap setelah tanggal" class="{{ $inp }}">
    <input type="date" name="to" value="{{ request('to') }}" title="Check-in sampai tanggal" class="{{ $inp }}">
    <div class="flex gap-2 sm:col-span-2 lg:col-span-6">
        <button class="rounded-xl bg-brand-600 px-5 py-2 text-sm font-bold text-white hover:bg-brand-700">Filter</button>
        <a href="{{ route('reservations.index') }}" class="rounded-xl bg-slate-100 px-5 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-200">Reset</a>
        @if ($isStaff)<a href="{{ route('reservations.create') }}" class="ml-auto inline-flex items-center gap-2 rounded-xl bg-forest-800 px-5 py-2 text-sm font-bold text-white shadow hover:bg-forest-900 transition"><x-icon name="plus" class="h-4 w-4" /> <span>Reservasi Baru</span></a>@endif
    </div>
</form>

<div class="mt-5 overflow-x-auto rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
            <tr>
                <th class="px-4 py-3">Kode</th><th class="px-4 py-3">Tamu</th><th class="px-4 py-3">Kamar</th>
                <th class="px-4 py-3">Menginap</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Pembayaran</th>
                <th class="px-4 py-3">Kanal</th><th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($reservations as $r)
                <tr class="hover:bg-slate-50">
                    <td class="whitespace-nowrap px-4 py-3 font-mono text-xs font-semibold">{{ $r->code }}</td>
                    <td class="px-4 py-3"><p class="font-semibold text-slate-900">{{ $r->guest_name }}</p><p class="text-xs text-slate-400">{{ $r->guest_city }}</p></td>
                    <td class="whitespace-nowrap px-4 py-3">{{ $r->room->code }}<p class="text-xs text-slate-400">{{ $r->room->type->name }}</p></td>
                    <td class="whitespace-nowrap px-4 py-3">{{ $r->check_in->translatedFormat('d M') }} → {{ $r->check_out->translatedFormat('d M Y') }}<p class="text-xs text-slate-400">{{ $r->nights }} malam</p></td>
                    <td class="px-4 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-semibold ring-1 {{ $r->statusClasses() }}">{{ $r->statusLabel() }}</span></td>
                    <td class="whitespace-nowrap px-4 py-3">
                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold ring-1 {{ $r->paymentStatusClasses() }}">{{ $r->paymentStatusLabel() }}</span>
                        <p class="mt-1 text-xs text-slate-500">{{ rupiah($r->paid_amount) }} / {{ rupiah($r->total_price) }}</p>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-xs text-slate-500">{{ $r->channelLabel() }}</td>
                    <td class="whitespace-nowrap px-4 py-3 text-right"><a href="{{ route('reservations.show', $r) }}" class="font-semibold text-brand-700 hover:underline">Detail</a></td>
                </tr>
            @empty
                <tr><td colspan="8" class="px-4 py-10 text-center text-slate-400">Belum ada reservasi yang cocok.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $reservations->links() }}</div>
@endsection
