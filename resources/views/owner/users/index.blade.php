@extends('layouts.app')
@section('title', 'Pengguna')
@section('heading', 'Pengguna')
@section('content')
<div class="mb-4 flex items-center justify-between gap-3">
    <p class="max-w-2xl text-sm text-slate-500">Owner memantau & mengelola data master. Pengelola (Admin Sosmed/Lapangan) mengoperasikan reservasi harian.</p>
    <a href="{{ route('owner.users.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-forest-800 px-5 py-2.5 text-sm font-bold text-white shadow transition hover:bg-forest-900">
        <x-icon name="plus" class="h-4 w-4" />
        <span>Tambah Pengguna</span>
    </a>
</div>
<div class="overflow-x-auto rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500"><tr><th class="px-4 py-3">Nama</th><th class="px-4 py-3">Email</th><th class="px-4 py-3">Peran</th><th class="px-4 py-3">Status</th><th class="px-4 py-3"></th></tr></thead>
        <tbody class="divide-y divide-slate-100">
            @foreach ($users as $u)
                <tr>
                    <td class="px-4 py-3 font-semibold text-slate-900">{{ $u->name }}<p class="text-xs font-normal text-slate-400">{{ $u->phone }}</p></td>
                    <td class="px-4 py-3">{{ $u->email }}</td>
                    <td class="px-4 py-3">{{ $u->roleLabel() }}</td>
                    <td class="px-4 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $u->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $u->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                    <td class="px-4 py-3 text-right"><a href="{{ route('owner.users.edit', $u) }}" class="font-semibold text-brand-700 hover:underline">Ubah</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
