@extends('layouts.app')
@section('title', 'Ubah Reservasi '.$reservation->code)
@section('heading', 'Ubah / Perpanjang Reservasi '.$reservation->code)
@section('content')
    <p class="mb-4 max-w-3xl text-sm text-slate-500">Setiap perubahan tanggal atau kamar diperiksa ulang terhadap reservasi lain. Total tagihan dihitung ulang dan sisa pembayaran diperbarui otomatis.</p>
    @include('staff.reservations._form', ['prefill' => []])
@endsection
