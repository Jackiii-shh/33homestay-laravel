@extends('layouts.app')
@section('title', 'Unit Kamar')
@section('heading', 'Unit Kamar')
@section('content')
<div class="mb-4 flex items-center justify-between gap-3">
    <p class="max-w-2xl text-sm text-slate-500">Daftar unit fisik. Jumlah unit contoh hanyalah data awal - sesuaikan dengan kondisi sebenarnya.</p>
    <a href="{{ route('owner.rooms.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-forest-800 px-5 py-2.5 text-sm font-bold text-white shadow transition hover:bg-forest-900">
        <x-icon name="plus" class="h-4 w-4" />
        <span>Tambah Unit</span>
    </a>
</div>
<div class="overflow-x-auto rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500"><tr><th class="px-4 py-3">Kode</th><th class="px-4 py-3">Tipe</th><th class="px-4 py-3">Kondisi</th><th class="px-4 py-3">Reservasi</th><th class="px-4 py-3">Aktif</th><th class="px-4 py-3"></th></tr></thead>
        <tbody class="divide-y divide-slate-100">
            @foreach ($rooms as $room)
                <tr>
                    <td class="px-4 py-3 font-bold text-slate-900">{{ $room->code }}</td>
                    <td class="px-4 py-3">{{ $room->type->name }}</td>
                    <td class="px-4 py-3">{{ \App\Models\Room::STATUSES[$room->status] ?? $room->status }}</td>
                    <td class="px-4 py-3">{{ $room->reservations_count }}</td>
                    <td class="px-4 py-3">{{ $room->is_active ? 'Ya' : 'Tidak' }}</td>
                    <td class="px-4 py-3 text-right"><a href="{{ route('owner.rooms.edit', $room) }}" class="font-semibold text-brand-700 hover:underline">Ubah</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
