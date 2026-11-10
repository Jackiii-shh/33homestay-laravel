@extends('layouts.public')
@section('title', 'Verifikasi Invoice '.$invoice->number)

@section('content')
@php $r = $invoice->reservation; @endphp
<main class="min-h-screen bg-warm-50 px-4 py-12">
    <div class="mx-auto max-w-lg">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-bold text-forest-800 hover:underline">
            <x-icon name="arrow-left" class="h-4 w-4" />
            <span>Kembali ke {{ config('homestay.name') }}</span>
        </a>

        <div class="mt-4 overflow-hidden rounded-2xl bg-white shadow-md ring-1 ring-slate-200">
            <div class="bg-forest-800 px-6 py-5 text-white">
                <p class="text-[11px] font-bold uppercase tracking-wider text-sand-300">Hasil Verifikasi Dokumen</p>
                <p class="mt-1 flex items-center gap-2 text-xl font-bold">
                    <x-icon name="check-circle" class="h-6 w-6 text-emerald-400 flex-shrink-0" />
                    <span>Dokumen ini ASLI &amp; Terverifikasi</span>
                </p>
                <p class="mt-1 text-xs text-forest-200">Tercatat secara resmi di sistem {{ config('homestay.name') }}.</p>
            </div>

            <dl class="divide-y divide-slate-100 px-6 py-2 text-xs sm:text-sm">
                @foreach ([
                    'Jenis dokumen' => $invoice->title(),
                    'Nomor' => $invoice->number,
                    'Diterbitkan' => $invoice->issued_at->translatedFormat('d F Y H:i').' WIB',
                    'Kode reservasi' => $r->code,
                    'Tamu' => $masked,
                    'Kamar' => $r->room->type->name,
                    'Menginap' => $r->check_in->translatedFormat('d M Y').' - '.$r->check_out->translatedFormat('d M Y').' ('.$r->nights.' malam)',
                    'Nominal pada dokumen ini' => rupiah($invoice->amount),
                    'Total tagihan reservasi' => rupiah($r->total_price),
                    'Total terbayar saat ini' => rupiah($r->paid_amount),
                    'Status pembayaran' => $r->paymentStatusLabel(),
                ] as $label => $value)
                    <div class="flex justify-between gap-4 py-3">
                        <dt class="text-slate-500 font-medium">{{ $label }}</dt>
                        <dd class="text-right font-bold text-slate-800">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>

        <p class="mt-4 text-center text-xs text-slate-500">Cocokkan nominal di atas dengan bukti transfer Anda. Bila ada ketidaksesuaian, segera hubungi pengelola melalui WhatsApp.</p>
    </div>
</main>
@endsection
