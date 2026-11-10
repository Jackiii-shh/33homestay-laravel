@extends('layouts.app')
@section('title', 'Log Aktivitas')
@section('heading', 'Log Aktivitas (Audit Trail)')

@section('content')
@php $inp = 'rounded-xl border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500'; @endphp
<p class="mb-4 max-w-3xl text-sm text-slate-500">Setiap pembuatan/perubahan reservasi, pembayaran, dan percobaan double booking yang ditolak tercatat di sini beserta pelaku dan waktunya, sehingga penyelewengan dan kesalahan pencatatan dapat ditelusuri.</p>

<form method="GET" class="grid gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:grid-cols-2 lg:grid-cols-5">
    <select name="action" class="{{ $inp }}"><option value="">Semua aktivitas</option>
        @foreach ($actions as $k => $l)<option value="{{ $k }}" @selected(request('action') === $k)>{{ $l }}</option>@endforeach</select>
    <select name="user" class="{{ $inp }}"><option value="">Semua pengguna</option>
        @foreach ($users as $u)<option value="{{ $u->id }}" @selected((string) request('user') === (string) $u->id)>{{ $u->name }}</option>@endforeach</select>
    <input type="date" name="from" value="{{ request('from') }}" class="{{ $inp }}">
    <input type="date" name="to" value="{{ request('to') }}" class="{{ $inp }}">
    <div class="flex gap-2"><button class="rounded-xl bg-brand-600 px-5 py-2 text-sm font-bold text-white hover:bg-brand-700">Filter</button><a href="{{ route('owner.audit') }}" class="rounded-xl bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-600">Reset</a></div>
</form>

<div class="mt-5 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
    <ul class="divide-y divide-slate-100">
        @forelse ($logs as $log)
            <li class="flex gap-4 px-5 py-4 {{ $log->action === 'booking_conflict_blocked' ? 'bg-rose-50/60' : '' }}">
                <div class="w-36 flex-shrink-0 text-xs text-slate-400">{{ $log->created_at->translatedFormat('d M Y') }}<br>{{ $log->created_at->format('H:i:s') }}</div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-bold {{ $log->action === 'booking_conflict_blocked' ? 'text-rose-700' : 'text-slate-800' }}">{{ $log->actionLabel() }}</p>
                    <p class="text-sm text-slate-600">{{ $log->description }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ $log->user?->name ?? 'Sistem' }}@if ($log->ip_address) · {{ $log->ip_address }}@endif
                        @if ($log->subject_type === \App\Models\Reservation::class && $log->subject_id) · <a href="{{ route('reservations.show', $log->subject_id) }}" class="font-semibold text-brand-700 hover:underline">buka reservasi</a>@endif</p>
                </div>
            </li>
        @empty
            <li class="px-5 py-10 text-center text-slate-400">Tidak ada aktivitas yang cocok.</li>
        @endforelse
    </ul>
</div>
<div class="mt-4">{{ $logs->links() }}</div>
@endsection
