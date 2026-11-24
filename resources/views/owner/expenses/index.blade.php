@extends('layouts.app')
@section('title', 'Laporan Biaya & Laba Bersih (P&L)')
@section('heading', 'Biaya Operasional & Laba Bersih')

@section('content')
@php
    $card = 'rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200';
    $inp = 'rounded-xl border-slate-300 text-sm focus:border-forest-600 focus:ring-forest-600';
@endphp

<!-- Filter Bulan & Ringkasan -->
<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <form method="GET" class="flex items-center gap-3">
        <label class="text-sm font-semibold text-slate-700">Pilih Bulan:</label>
        <input type="month" name="month" value="{{ $month }}" onchange="this.form.submit()" class="{{ $inp }}">
    </form>

    <button onclick="document.getElementById('modalTambahPengeluaran').classList.remove('hidden')" class="inline-flex items-center gap-2 rounded-xl bg-forest-800 px-4 py-2.5 text-sm font-bold text-white hover:bg-forest-900 shadow-sm transition">
        <x-icon name="plus" class="h-4 w-4" />
        <span>Catat Pengeluaran Baru</span>
    </button>
</div>

<!-- Kartu Metrik Laba Rugi (P&L) Sesuai Pembukuan Owner -->
<div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div class="{{ $card }}">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Total Omzet (Masuk)</p>
        <p class="mt-2 text-2xl font-extrabold text-forest-800">{{ rupiah($omzetBulan) }}</p>
        <p class="mt-1 text-xs text-slate-500">Bulan {{ $start->translatedFormat('F Y') }}</p>
    </div>

    <div class="{{ $card }}">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Biaya Tetap (Fixed)</p>
        <p class="mt-2 text-2xl font-extrabold text-slate-700">{{ rupiah($totalTetap) }}</p>
        <p class="mt-1 text-xs text-slate-500">Gaji, WiFi, Netflix, Sampah, Kamar 5</p>
    </div>

    <div class="{{ $card }}">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Biaya Variabel (Ops)</p>
        <p class="mt-2 text-2xl font-extrabold text-amber-700">{{ rupiah($totalVariabel) }}</p>
        <p class="mt-1 text-xs text-slate-500">Token Listrik, Belanja, Galon, Kasbon</p>
    </div>

    <div class="{{ $card }} {{ $netProfit >= 0 ? 'bg-emerald-50/50 ring-emerald-200' : 'bg-rose-50/50 ring-rose-200' }}">
        <p class="text-xs font-semibold uppercase tracking-wide {{ $netProfit >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">Laba Bersih (Net Profit)</p>
        <p class="mt-2 text-2xl font-extrabold {{ $netProfit >= 0 ? 'text-emerald-800' : 'text-rose-800' }}">{{ rupiah($netProfit) }}</p>
        <p class="mt-1 text-xs text-slate-500">Omzet dikurangi Seluruh Beban</p>
    </div>
</div>

<!-- Token Listrik per Kamar (K1 - K5) -->
@if (!empty($listrikPerKamar))
<div class="mt-5 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
    <div class="flex items-center gap-2 mb-3">
        <x-icon name="check-circle" class="h-5 w-5 text-amber-600" />
        <h3 class="font-bold text-slate-900">Biaya Token Listrik per Kamar (Bulan Ini)</h3>
    </div>
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
        @foreach (['K1', 'K2', 'K3', 'K4', 'K5'] as $code)
            <div class="rounded-xl border border-slate-100 bg-slate-50 p-3 text-center">
                <span class="text-xs font-semibold text-slate-500">Kamar {{ $code }}</span>
                <p class="mt-1 text-base font-bold text-slate-800">{{ rupiah($listrikPerKamar[$code] ?? 0) }}</p>
            </div>
        @endforeach
    </div>
</div>
@endif

<!-- Tabel Daftar Pengeluaran -->
<div class="mt-5 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
    <div class="border-b border-slate-100 px-5 py-4">
        <h3 class="font-bold text-slate-900">Rincian Pengeluaran Bulan {{ $start->translatedFormat('F Y') }}</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Jenis Biaya</th>
                    <th class="px-4 py-3">Kategori</th>
                    <th class="px-4 py-3">Deskripsi Pengeluaran</th>
                    <th class="px-4 py-3">Kamar</th>
                    <th class="px-4 py-3 text-right">Nominal</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($expenses as $e)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="whitespace-nowrap px-4 py-3 font-medium text-slate-700">{{ $e->expense_date->translatedFormat('d M Y') }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $e->type === 'tetap' ? 'bg-slate-100 text-slate-700' : 'bg-amber-100 text-amber-800' }}">
                                {{ $e->type === 'tetap' ? 'Biaya Tetap' : 'Biaya Variabel' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $e->categoryLabel() }}</td>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $e->name }}</td>
                        <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $e->room_code ?? '-' }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right font-bold text-slate-900">{{ rupiah($e->amount) }}</td>
                        <td class="px-4 py-3 text-center">
                            <form method="POST" action="{{ route('owner.expenses.destroy', $e) }}" onsubmit="return confirm('Hapus catatan pengeluaran ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-rose-600 hover:text-rose-800 font-semibold">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-slate-400">Belum ada catatan pengeluaran pada bulan ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Catat Pengeluaran Baru -->
<div id="modalTambahPengeluaran" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 hidden">
    <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl ring-1 ring-slate-200">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="font-bold text-slate-900 text-lg">Catat Pengeluaran Baru</h3>
            <button onclick="document.getElementById('modalTambahPengeluaran').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
        </div>
        <form method="POST" action="{{ route('owner.expenses.store') }}" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-600">Jenis Biaya *</label>
                <select name="type" required class="{{ $inp }} mt-1 w-full">
                    <option value="variabel">Biaya Variabel (Token, Belanja, Galon, Bonus, Kasbon)</option>
                    <option value="tetap">Biaya Tetap (Gaji, WiFi, Netflix, Sampah, Kamar 5)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600">Kategori Biaya *</label>
                <select name="category" required class="{{ $inp }} mt-1 w-full">
                    <option value="listrik">Listrik / Token Kamar</option>
                    <option value="gaji">Gaji Karyawan</option>
                    <option value="makanan">Makanan / Galon / Kopi / Gas</option>
                    <option value="barang">Barang / Perlengkapan / Perbaikan</option>
                    <option value="bonus">Bonus Staf</option>
                    <option value="wifi">Internet WiFi</option>
                    <option value="netflix">Netflix</option>
                    <option value="sampah">Iuran Sampah</option>
                    <option value="sewa_kamar">Sewa Kamar 5</option>
                    <option value="kasbon">Kasbon Pengelola</option>
                    <option value="lainnya">Lainnya</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600">Deskripsi / Nama Barang *</label>
                <input type="text" name="name" required placeholder="Contoh: Token Listrik K1 / Beras 5kg / Galon" class="{{ $inp }} mt-1 w-full">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600">Nominal (Rp) *</label>
                    <input type="number" name="amount" required min="1000" step="500" placeholder="100000" class="{{ $inp }} mt-1 w-full">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600">Tanggal Pengeluaran *</label>
                    <input type="date" name="expense_date" required value="{{ today()->toDateString() }}" class="{{ $inp }} mt-1 w-full">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600">Kamar Terkait (Opsional)</label>
                <select name="room_code" class="{{ $inp }} mt-1 w-full">
                    <option value="">— Umum / Seluruh Properti —</option>
                    <option value="K1">K1 (Standard AC)</option>
                    <option value="K2">K2 (Standard AC)</option>
                    <option value="K3">K3 (Mezanin)</option>
                    <option value="K4">K4 (Standard Kipas)</option>
                    <option value="K5">K5 (Standard AC)</option>
                    <option value="TH-1">Tera House</option>
                </select>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modalTambahPengeluaran').classList.add('hidden')" class="rounded-xl px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 transition">Batal</button>
                <button type="submit" class="rounded-xl bg-forest-800 px-5 py-2 text-sm font-bold text-white hover:bg-forest-900 transition">Simpan Pengeluaran</button>
            </div>
        </form>
    </div>
</div>
@endsection
