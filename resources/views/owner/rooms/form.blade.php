@extends('layouts.app')
@section('title', $room->exists ? 'Ubah Unit Kamar' : 'Tambah Unit Kamar')
@section('heading', $room->exists ? 'Ubah Unit Kamar' : 'Tambah Unit Kamar')
@section('content')
@php $inp = 'mt-1 w-full rounded-xl border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500'; @endphp
<form method="POST" action="{{ $room->exists ? route('owner.rooms.update', $room) : route('owner.rooms.store') }}" class="max-w-xl space-y-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
    @csrf @if ($room->exists) @method('PUT') @endif
    <label class="block text-sm font-semibold text-slate-700">Tipe kamar
        <select name="room_type_id" required class="{{ $inp }}">@foreach ($types as $t)<option value="{{ $t->id }}" @selected((int) old('room_type_id', $room->room_type_id) === $t->id)>{{ $t->name }}</option>@endforeach</select></label>
    <label class="block text-sm font-semibold text-slate-700">Kode unit<input type="text" name="code" value="{{ old('code', $room->code) }}" required maxlength="30" placeholder="mis. AC-4" class="{{ $inp }}"></label>
    <label class="block text-sm font-semibold text-slate-700">Kondisi
        <select name="status" class="{{ $inp }}">@foreach (\App\Models\Room::STATUSES as $k => $l)<option value="{{ $k }}" @selected(old('status', $room->status) === $k)>{{ $l }}</option>@endforeach</select></label>
    <label class="block text-sm font-semibold text-slate-700">Catatan<textarea name="notes" rows="2" maxlength="300" class="{{ $inp }}">{{ old('notes', $room->notes) }}</textarea></label>
    <label class="flex items-center gap-2 text-sm font-semibold text-slate-700"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $room->is_active)) class="rounded border-slate-300 text-brand-600 focus:ring-brand-500"> Unit aktif (dapat dipesan)</label>
    <div class="flex gap-3"><button class="rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-bold text-white hover:bg-brand-700">Simpan</button><a href="{{ route('owner.rooms.index') }}" class="rounded-xl bg-slate-100 px-6 py-2.5 text-sm font-semibold text-slate-600">Batal</a></div>
</form>
@endsection
