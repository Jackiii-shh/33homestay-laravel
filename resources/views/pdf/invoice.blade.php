@php
    $r = $invoice->reservation;
    $paidUntil = (int) $r->payments->where('id', '<=', $invoice->payment_id)->sum('amount');
    $remaining = max(0, $r->total_price - $paidUntil);
    $statusText = $remaining === 0 ? 'LUNAS' : 'BELUM LUNAS';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $invoice->number }}</title>
    <style>
        @page { margin: 28px 30px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1e293b; }
        table { width: 100%; border-collapse: collapse; }
        .brand { font-size: 18px; font-weight: bold; color: #0f766e; }
        .muted { color: #64748b; }
        .title { font-size: 15px; font-weight: bold; text-align: right; color: #0f766e; }
        .box { border: 1px solid #e2e8f0; padding: 8px 10px; }
        .label { font-size: 8px; text-transform: uppercase; letter-spacing: .5px; color: #94a3b8; }
        .items th { background: #f1f5f9; text-align: left; padding: 6px 8px; font-size: 9px; }
        .items td { padding: 7px 8px; border-bottom: 1px solid #e2e8f0; }
        .right { text-align: right; }
        .total td { font-weight: bold; padding: 5px 8px; }
        .badge { display: inline-block; padding: 3px 10px; font-weight: bold; font-size: 10px; color: #fff; background: {{ $remaining === 0 ? '#059669' : '#d97706' }}; }
        .verify { margin-top: 14px; border: 1px dashed #94a3b8; padding: 8px 10px; font-size: 8.5px; }
        .foot { margin-top: 10px; font-size: 8px; color: #64748b; text-align: center; }
    </style>
</head>
<body>
    <table>
        <tr>
            <td style="width:60%">
                <table style="width:auto">
                    <tr>
                        <td style="vertical-align:middle; padding-right:10px; width:46px;">
                            <img src="{{ public_path('images/logo.jpg') }}" style="width:44px; height:44px; border-radius:22px;">
                        </td>
                        <td style="vertical-align:middle;">
                            <div class="brand">{{ config('homestay.name') }}</div>
                            <div class="muted">{{ config('homestay.address') }}<br>WhatsApp: +{{ config('homestay.whatsapp') }}</div>
                        </td>
                    </tr>
                </table>
            </td>
            <td>
                <div class="title">{{ $invoice->title() }}</div>
                <div class="right muted">No. {{ $invoice->number }}<br>Terbit: {{ $invoice->issued_at->translatedFormat('d F Y, H:i') }} WIB</div>
            </td>
        </tr>
    </table>

    <table style="margin-top:14px">
        <tr>
            <td class="box" style="width:50%">
                <div class="label">Tamu</div>
                <strong>{{ $r->guest_name }}</strong><br>
                {{ $r->guest_city }}@if ($r->guest_phone) · {{ $r->guest_phone }}@endif
            </td>
            <td style="width:2%"></td>
            <td class="box">
                <div class="label">Reservasi</div>
                <strong>{{ $r->code }}</strong><br>
                {{ $r->room->type->name }} ({{ $r->room->code }}) · {{ $r->guest_count }} tamu<br>
                {{ $r->check_in->translatedFormat('d M Y') }} – {{ $r->check_out->translatedFormat('d M Y') }} ({{ $r->nights }} malam)
            </td>
        </tr>
    </table>

    <table class="items" style="margin-top:14px">
        <thead><tr><th>Deskripsi</th><th class="right">Harga</th><th class="right">Qty</th><th class="right">Jumlah</th></tr></thead>
        <tbody>
            <tr>
                <td>Menginap {{ $r->room->type->name }}</td>
                <td class="right">{{ rupiah($r->price_per_night) }}</td>
                <td class="right">{{ $r->nights }} malam</td>
                <td class="right">{{ rupiah($r->total_price) }}</td>
            </tr>
        </tbody>
    </table>

    <table class="total" style="margin-top:6px">
        <tr><td></td><td class="right muted" style="width:46%">Total tagihan</td><td class="right" style="width:26%">{{ rupiah($r->total_price) }}</td></tr>
        <tr><td></td><td class="right" style="color:#0f766e">Pembayaran ini ({{ $invoice->payment->typeLabel() }} · {{ $invoice->payment->methodLabel() }}, {{ $invoice->payment->paid_at->translatedFormat('d M Y H:i') }})</td><td class="right" style="color:#0f766e">{{ rupiah($invoice->amount) }}</td></tr>
        <tr><td></td><td class="right muted">Total terbayar s.d. dokumen ini</td><td class="right">{{ rupiah($paidUntil) }}</td></tr>
        <tr><td></td><td class="right">Sisa tagihan</td><td class="right" style="font-size:12px">{{ rupiah($remaining) }}</td></tr>
    </table>

    <p style="margin-top:10px"><span class="badge">{{ $statusText }}</span>
        @if ($remaining > 0)<span class="muted"> Sisa pembayaran dilunasi saat check-in.</span>@endif</p>

    <div class="verify">
        <strong>Verifikasi keaslian dokumen</strong><br>
        Pindai/buka tautan berikut untuk mencocokkan nominal dengan catatan sistem:<br>
        {{ $invoice->verifyUrl() }}
    </div>

    <div class="foot">
        Diterbitkan oleh sistem {{ config('homestay.name') }}@if ($invoice->issuer) · dicatat oleh {{ $invoice->issuer->name }}@endif.<br>
        Check-in mulai {{ config('homestay.checkin_time') }} · Check-out maks. {{ config('homestay.checkout_time') }}. Dokumen ini sah tanpa tanda tangan basah.
    </div>
</body>
</html>
