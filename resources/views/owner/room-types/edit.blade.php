@extends('layouts.app')
@section('title', $type->exists ? 'Ubah Tipe Kamar' : 'Tambah Tipe Kamar')
@section('heading', $type->exists ? 'Ubah Tipe Kamar' : 'Tambah Tipe Kamar')
@section('content')
@php $inp = 'mt-1 w-full rounded-xl border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500'; @endphp
<form method="POST" action="{{ $type->exists ? route('owner.room-types.update', $type) : route('owner.room-types.store') }}" class="max-w-2xl space-y-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
    @csrf @if ($type->exists) @method('PUT') @endif
    <label class="block text-sm font-semibold text-slate-700">Nama tipe<input type="text" name="name" value="{{ old('name', $type->name) }}" required class="{{ $inp }}"></label>
    <div class="grid gap-4 sm:grid-cols-2">
        <label class="block text-sm font-semibold text-slate-700">Kapasitas (orang)<input type="number" name="capacity" min="1" max="20" value="{{ old('capacity', $type->capacity) }}" required class="{{ $inp }}"></label>
        <label class="block text-sm font-semibold text-slate-700">Harga per malam (Rp)<input type="number" name="price_per_night" min="0" step="1000" value="{{ old('price_per_night', $type->price_per_night) }}" required class="{{ $inp }}"></label>
    </div>
    <label class="block text-sm font-semibold text-slate-700">Deskripsi<textarea name="description" rows="2" maxlength="500" class="{{ $inp }}">{{ old('description', $type->description) }}</textarea></label>
    <label class="block text-sm font-semibold text-slate-700">Fasilitas <span class="font-normal text-slate-400">(satu per baris)</span><textarea name="facilities" rows="4" class="{{ $inp }}">{{ old('facilities', implode("\n", $type->facilities ?? [])) }}</textarea></label>
    <label class="flex items-center gap-2 text-sm font-semibold text-slate-700"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $type->is_active)) class="rounded border-slate-300 text-brand-600 focus:ring-brand-500"> Tampilkan & dapat dipesan</label>
    <div class="flex gap-3"><button class="rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-bold text-white hover:bg-brand-700">Simpan</button><a href="{{ route('owner.room-types.index') }}" class="rounded-xl bg-slate-100 px-6 py-2.5 text-sm font-semibold text-slate-600">Batal</a></div>
</form>
@endsection
