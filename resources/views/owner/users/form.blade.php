@extends('layouts.app')
@section('title', $user->exists ? 'Ubah Pengguna' : 'Tambah Pengguna')
@section('heading', $user->exists ? 'Ubah Pengguna' : 'Tambah Pengguna')
@section('content')
@php $inp = 'mt-1 w-full rounded-xl border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500'; @endphp
<form method="POST" action="{{ $user->exists ? route('owner.users.update', $user) : route('owner.users.store') }}" class="max-w-xl space-y-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
    @csrf @if ($user->exists) @method('PUT') @endif
    <label class="block text-sm font-semibold text-slate-700">Nama<input type="text" name="name" value="{{ old('name', $user->name) }}" required class="{{ $inp }}"></label>
    <label class="block text-sm font-semibold text-slate-700">Email<input type="email" name="email" value="{{ old('email', $user->email) }}" required class="{{ $inp }}"></label>
    <label class="block text-sm font-semibold text-slate-700">No. HP<input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="{{ $inp }}"></label>
    <label class="block text-sm font-semibold text-slate-700">Peran
        <select name="role" class="{{ $inp }}">@foreach (\App\Models\User::ROLES as $k => $l)<option value="{{ $k }}" @selected(old('role', $user->role) === $k)>{{ $l }}</option>@endforeach</select></label>
    <label class="block text-sm font-semibold text-slate-700">Kata sandi {{ $user->exists ? '(kosongkan bila tidak diganti)' : '' }}
        <input type="password" name="password" minlength="8" {{ $user->exists ? '' : 'required' }} autocomplete="new-password" class="{{ $inp }}"></label>
    <label class="flex items-center gap-2 text-sm font-semibold text-slate-700"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active)) class="rounded border-slate-300 text-brand-600 focus:ring-brand-500"> Akun aktif</label>
    <div class="flex gap-3"><button class="rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-bold text-white hover:bg-brand-700">Simpan</button><a href="{{ route('owner.users.index') }}" class="rounded-xl bg-slate-100 px-6 py-2.5 text-sm font-semibold text-slate-600">Batal</a></div>
</form>
@endsection
