@extends('layouts.app')
@section('title', 'Tipe & Harga Kamar')
@section('heading', 'Tipe & Harga Kamar')
@section('content')
<div class="mb-4 flex flex-wrap items-start justify-between gap-3">
<p class="max-w-3xl text-sm text-slate-500">Harga di sini menjadi dasar perhitungan reservasi baru. Reservasi yang sudah dibuat tetap memakai harga saat dibuat. Harga bawaan hanyalah data contoh - sesuaikan dengan harga sebenarnya.</p>
<a href="{{ route('owner.room-types.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-forest-800 px-5 py-2.5 text-sm font-bold text-white shadow transition hover:bg-forest-900">
    <x-icon name="plus" class="h-4 w-4" />
    <span>Tambah Tipe Kamar</span>
</a>
</div>
<div class="overflow-x-auto rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500"><tr><th class="px-4 py-3">Tipe</th><th class="px-4 py-3">Kapasitas</th><th class="px-4 py-3">Harga / malam</th><th class="px-4 py-3">Unit</th><th class="px-4 py-3">Status</th><th class="px-4 py-3"></th></tr></thead>
        <tbody class="divide-y divide-slate-100">
            @foreach ($types as $t)
                <tr>
                    <td class="px-4 py-3"><p class="font-semibold text-slate-900">{{ $t->name }}</p><p class="text-xs text-slate-400">{{ $t->description }}</p></td>
                    <td class="px-4 py-3">{{ $t->capacity }} orang</td>
                    <td class="px-4 py-3 font-bold">{{ rupiah($t->price_per_night) }}</td>
                    <td class="px-4 py-3">{{ $t->rooms_count }}</td>
                    <td class="px-4 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $t->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $t->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                    <td class="px-4 py-3 text-right"><a href="{{ route('owner.room-types.edit', $t) }}" class="font-semibold text-brand-700 hover:underline">Ubah</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
