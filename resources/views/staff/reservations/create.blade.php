@extends('layouts.app')
@section('title', 'Reservasi Baru')
@section('heading', 'Reservasi Baru')
@section('content')
    <p class="mb-4 max-w-3xl text-sm text-slate-500">Catat pesanan yang masuk dari WhatsApp Business, OTA, atau walk-in. Kamar yang ditampilkan hanya yang <strong>kosong</strong> pada tanggal terpilih, dan tanggal otomatis terblokir setelah disimpan.</p>
    @include('staff.reservations._form')
@endsection
