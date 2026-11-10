@extends('layouts.app')
@section('title', 'Profil')
@section('heading', 'Profil & Kata Sandi')
@section('content')
@php $inp = 'mt-1 w-full rounded-xl border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500'; @endphp
<div class="grid max-w-4xl gap-6 md:grid-cols-2">
    <form method="POST" action="{{ route('profile.update') }}" class="space-y-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        @csrf @method('PUT')
        <h2 class="font-bold text-slate-900">Data akun <span class="ml-1 rounded-full bg-brand-50 px-2 py-0.5 text-xs text-brand-700">{{ $user->roleLabel() }}</span></h2>
        <label class="block text-sm font-semibold text-slate-700">Nama<input type="text" name="name" value="{{ old('name', $user->name) }}" required class="{{ $inp }}"></label>
        <label class="block text-sm font-semibold text-slate-700">Email<input type="email" name="email" value="{{ old('email', $user->email) }}" required class="{{ $inp }}"></label>
        <label class="block text-sm font-semibold text-slate-700">No. HP<input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="{{ $inp }}"></label>
        <button class="rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-bold text-white hover:bg-brand-700">Simpan profil</button>
    </form>
    <form method="POST" action="{{ route('profile.password') }}" class="space-y-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        @csrf @method('PUT')
        <h2 class="font-bold text-slate-900">Ganti kata sandi</h2>
        <label class="block text-sm font-semibold text-slate-700">Kata sandi saat ini<input type="password" name="current_password" required autocomplete="current-password" class="{{ $inp }}"></label>
        <label class="block text-sm font-semibold text-slate-700">Kata sandi baru<input type="password" name="password" required minlength="8" autocomplete="new-password" class="{{ $inp }}"></label>
        <label class="block text-sm font-semibold text-slate-700">Ulangi kata sandi baru<input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password" class="{{ $inp }}"></label>
        <button class="rounded-xl bg-slate-800 px-6 py-2.5 text-sm font-bold text-white hover:bg-slate-900">Ganti kata sandi</button>
    </form>
</div>
@endsection
