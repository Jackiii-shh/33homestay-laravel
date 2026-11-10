@extends('layouts.app')
@section('title', 'Reservasi '.$reservation->code)
@section('heading', 'Reservasi '.$reservation->code)

@section('content')
@php
    $r = $reservation;
    $isStaff = auth()->user()->isPengelola();
    $active = $r->isActiveBooking();
    $balance = $r->balance;
    $card = 'rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200';
    $inp = 'mt-1 w-full rounded-xl border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500';
    $btn = 'w-full rounded-xl px-4 py-2.5 text-sm font-bold';
    $defaultAmount = $r->status === \App\Models\Reservation::STATUS_PENDING ? $r->minimumDp() : $balance;
@endphp

<div class="mb-5 flex flex-wrap items-center gap-3">
    <a href="{{ route('reservations.index') }}" class="text-sm font-semibold text-brand-700 hover:underline">← Daftar reservasi</a>
    <span class="rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $r->statusClasses() }}">{{ $r->statusLabel() }}</span>
    <span class="rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $r->paymentStatusClasses() }}">{{ $r->paymentStatusLabel() }}</span>
    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $r->channelLabel() }}</span>
    @if ($isStaff && $active)
        <a href="{{ route('reservations.edit', $r) }}" class="ml-auto inline-flex items-center gap-1.5 rounded-xl bg-white px-4 py-2 text-xs sm:text-sm font-bold text-slate-700 shadow-sm ring-1 ring-slate-200 hover:bg-slate-50 transition">
            <x-icon name="clipboard-list" class="h-4 w-4 text-slate-400" />
            <span>Ubah / Perpanjang</span>
        </a>
    @endif
</div>

<div class="grid gap-6 lg:grid-cols-3">
    <div class="space-y-6 lg:col-span-2">
        {{-- Detail --}}
        <section class="{{ $card }}">
            <h2 class="font-bold text-slate-900">Detail reservasi</h2>
            <dl class="mt-4 grid gap-x-8 gap-y-3 text-sm sm:grid-cols-2">
                @foreach ([
                    'Tamu' => $r->guest_name,
                    'Asal kota' => $r->guest_city ?: '-',
                    'Jumlah tamu' => $r->guest_count.' orang',
                    'Kamar' => $r->room->code.' - '.$r->room->type->name,
                    'Check-in' => $r->check_in->translatedFormat('l, d F Y'),
                    'Check-out' => $r->check_out->translatedFormat('l, d F Y'),
                    'Durasi' => $r->nights.' malam × '.rupiah($r->price_per_night),
                    'Total tagihan' => rupiah($r->total_price),
                    'Sudah dibayar' => rupiah($r->paid_amount),
                    'Sisa tagihan' => rupiah($balance),
                    'Dibuat oleh' => ($r->creator?->name ?? 'Sistem').' · '.$r->created_at->translatedFormat('d M Y H:i'),
                ] as $label => $value)
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $label }}</dt><dd class="mt-0.5 font-semibold text-slate-800">{{ $value }}</dd></div>
                @endforeach
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">No. HP</dt>
                    <dd class="mt-0.5 font-semibold text-slate-800">
                        @if ($r->guest_phone)
                            {{ $r->guest_phone }} <a href="{{ wa_link($r->guest_phone) }}" target="_blank" rel="noopener" class="ml-1 text-xs font-bold text-green-700 hover:underline">chat WA</a>
                        @else - @endif
                    </dd>
                </div>
            </dl>

            @if ($r->notes)<p class="mt-4 rounded-xl bg-slate-50 p-3 text-sm text-slate-600 flex items-start gap-2"><x-icon name="clipboard-list" class="h-4 w-4 text-slate-400 flex-shrink-0 mt-0.5" /> <span>{{ $r->notes }}</span></p>@endif

            @if ($r->status === \App\Models\Reservation::STATUS_PENDING && $r->dp_deadline)
                <p class="mt-4 rounded-xl bg-amber-50 p-3 text-sm text-amber-800">⏳ Batas pembayaran DP: <strong>{{ $r->dp_deadline->translatedFormat('d M Y H:i') }}</strong> ({{ $r->dp_deadline->diffForHumans() }}). Lewat batas, reservasi batal otomatis dan tanggal dibuka kembali.</p>
            @endif
            @if (in_array($r->status, [\App\Models\Reservation::STATUS_CANCELLED, \App\Models\Reservation::STATUS_NO_SHOW], true))
                <p class="mt-4 rounded-xl bg-rose-50 p-3 text-sm text-rose-800">
                    {{ $r->status === \App\Models\Reservation::STATUS_NO_SHOW ? 'Tamu tidak datang (no-show).' : 'Dibatalkan: '.$r->cancel_reason.'.' }}
                    Refund: <strong>{{ rupiah($r->refund_amount) }}</strong>{{ $r->dp_forfeited ? ' · DP hangus' : '' }}.
                </p>
            @endif
            @if ($r->checked_in_at)<p class="mt-3 text-xs text-slate-500">Check-in aktual: {{ $r->checked_in_at->translatedFormat('d M Y H:i') }}@if ($r->checked_out_at) · Check-out aktual: {{ $r->checked_out_at->translatedFormat('d M Y H:i') }}@endif</p>@endif
        </section>

        {{-- Pembayaran & invoice --}}
        <section class="{{ $card }}">
            <h2 class="font-bold text-slate-900">Pembayaran & invoice</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-xs font-semibold uppercase tracking-wide text-slate-400">
                        <tr><th class="py-2 pr-4">Waktu</th><th class="py-2 pr-4">Jenis</th><th class="py-2 pr-4">Metode</th><th class="py-2 pr-4 text-right">Nominal</th><th class="py-2 pr-4">Dicatat</th><th class="py-2">Invoice</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($r->payments->sortBy('paid_at') as $p)
                            <tr>
                                <td class="whitespace-nowrap py-3 pr-4">{{ $p->paid_at->translatedFormat('d M Y H:i') }}</td>
                                <td class="py-3 pr-4 font-semibold">{{ $p->typeLabel() }}</td>
                                <td class="py-3 pr-4">{{ $p->methodLabel() }}</td>
                                <td class="whitespace-nowrap py-3 pr-4 text-right font-bold">{{ rupiah($p->amount) }}</td>
                                <td class="py-3 pr-4 text-xs text-slate-500">{{ $p->receiver?->name ?? 'Sistem' }}</td>
                                <td class="py-3">
                                    @if ($p->invoice)
                                        <p class="font-mono text-xs">{{ $p->invoice->number }}</p>
                                        <div class="mt-1 flex flex-wrap gap-2 text-xs font-bold">
                                            <a href="{{ route('invoices.pdf', $p->invoice) }}" class="text-brand-700 hover:underline">PDF</a>
                                            @if ($isStaff)<a href="{{ route('invoices.whatsapp', $p->invoice) }}" target="_blank" rel="noopener" class="text-green-700 hover:underline">Kirim WA</a>@endif
                                            <a href="{{ $p->invoice->verifyUrl() }}" target="_blank" rel="noopener" class="text-slate-500 hover:underline">Verifikasi</a>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-6 text-center text-slate-400">Belum ada pembayaran.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Riwayat --}}
        <section class="{{ $card }}">
            <h2 class="font-bold text-slate-900">Riwayat aktivitas</h2>
            <ol class="mt-4 space-y-3 border-l-2 border-slate-100 pl-4">
                @forelse ($logs as $log)
                    <li class="text-sm">
                        <p class="font-semibold {{ $log->action === 'booking_conflict_blocked' ? 'text-rose-700' : 'text-slate-800' }}">{{ $log->actionLabel() }}</p>
                        <p class="text-slate-600">{{ $log->description }}</p>
                        <p class="text-xs text-slate-400">{{ $log->created_at->translatedFormat('d M Y H:i') }} · {{ $log->user?->name ?? 'Sistem' }}</p>
                    </li>
                @empty
                    <li class="text-sm text-slate-400">Belum ada aktivitas.</li>
                @endforelse
            </ol>
        </section>
    </div>

    {{-- Panel aksi (hanya Pengelola) --}}
    <aside class="space-y-6">
        @if (! $isStaff)
            <div class="rounded-2xl bg-slate-100 p-5 text-sm text-slate-600 flex items-center gap-2.5">
                <x-icon name="info" class="h-5 w-5 text-slate-400 flex-shrink-0" />
                <span>Mode pemantau (Owner). Perubahan dilakukan oleh Pengelola.</span>
            </div>
        @else
            @if ($active && $balance > 0)
                <section id="pembayaran" class="{{ $card }} scroll-mt-20">
                    <h2 class="flex items-center gap-2 font-bold text-slate-900"><x-icon name="credit-card" class="h-4 w-4 text-forest-700" /> <span>Catat pembayaran</span></h2>
                    <p class="mt-1 text-xs text-slate-500">
                        Sisa tagihan <strong>{{ rupiah($balance) }}</strong>.
                        @if ($r->status === \App\Models\Reservation::STATUS_PENDING) DP minimal <strong>{{ rupiah($r->minimumDp()) }}</strong> ({{ config('homestay.dp_percentage') }}%). @endif
                        Setiap pembayaran otomatis menerbitkan invoice PDF.
                    </p>
                    <form method="POST" action="{{ route('reservations.payments.store', $r) }}" class="mt-4 space-y-3">
                        @csrf
                        <label class="block text-sm font-semibold text-slate-700">Nominal (Rp)
                            <input type="number" name="amount" min="1" max="{{ $balance }}" value="{{ old('amount', $defaultAmount) }}" required class="{{ $inp }}">
                        </label>
                        <div class="flex gap-2 text-xs">
                            <button type="button" onclick="this.closest('form').amount.value={{ $balance }}" class="rounded-lg bg-slate-100 px-3 py-1.5 font-semibold hover:bg-slate-200">Lunasi ({{ rupiah($balance) }})</button>
                            @if ($r->status === \App\Models\Reservation::STATUS_PENDING)
                                <button type="button" onclick="this.closest('form').amount.value={{ $r->minimumDp() }}" class="rounded-lg bg-slate-100 px-3 py-1.5 font-semibold hover:bg-slate-200">DP minimal</button>
                            @endif
                        </div>
                        <label class="block text-sm font-semibold text-slate-700">Metode
                            <select name="method" class="{{ $inp }}">
                                @foreach (\App\Models\Payment::METHODS as $key => $label)
                                    @unless ($key === 'ota')<option value="{{ $key }}" @selected(old('method', 'transfer') === $key)>{{ $label }}</option>@endunless
                                @endforeach
                            </select>
                        </label>
                        <label class="block text-sm font-semibold text-slate-700">Waktu diterima
                            <input type="datetime-local" name="paid_at" value="{{ old('paid_at', now()->format('Y-m-d\TH:i')) }}" class="{{ $inp }}">
                        </label>
                        <label class="block text-sm font-semibold text-slate-700">Catatan
                            <input type="text" name="note" maxlength="200" value="{{ old('note') }}" placeholder="mis. a.n. pengirim / no. referensi" class="{{ $inp }}">
                        </label>
                        <button class="{{ $btn }} bg-brand-600 text-white hover:bg-brand-700">Simpan & terbitkan invoice</button>
                    </form>
                </section>
            @endif

            @if ($active)
                <section class="{{ $card }}">
                    <h2 class="font-bold text-slate-900">Tindakan</h2>
                    <div class="mt-4 space-y-3">
                        @if ($r->status === \App\Models\Reservation::STATUS_CONFIRMED)
                            @if ($balance > 0)
                                <p class="rounded-xl bg-amber-50 p-3 text-xs text-amber-800">Check-in memerlukan pelunasan terlebih dahulu. Sisa <strong>{{ rupiah($balance) }}</strong> - catat di formulir pembayaran.</p>
                            @else
                                <form method="POST" action="{{ route('reservations.check-in', $r) }}">@csrf
                                    <button class="{{ $btn }} inline-flex items-center justify-center gap-1.5 bg-emerald-600 text-white hover:bg-emerald-700">
                                        <x-icon name="check-circle" class="h-4 w-4" /> Check-in
                                    </button>
                                </form>
                            @endif
                            @if ($r->checkInCutoff()->isPast())
                                <form method="POST" action="{{ route('reservations.no-show', $r) }}" onsubmit="return confirm('Tandai NO-SHOW? DP hangus dan kamar dibuka kembali.')">@csrf
                                    <button class="{{ $btn }} inline-flex items-center justify-center gap-1.5 bg-orange-100 text-orange-800 hover:bg-orange-200">
                                        <x-icon name="user-x" class="h-4 w-4" /> Tandai No-show
                                    </button>
                                </form>
                            @else
                                <p class="text-xs text-slate-400">No-show dapat ditandai setelah {{ $r->checkInCutoff()->translatedFormat('d M H:i') }}.</p>
                            @endif
                        @endif

                        @if ($r->status === \App\Models\Reservation::STATUS_CHECKED_IN)
                            @if ($balance > 0)
                                <p class="rounded-xl bg-amber-50 p-3 text-xs text-amber-800">Masih ada sisa tagihan <strong>{{ rupiah($balance) }}</strong>. Selesaikan sebelum check-out.</p>
                            @else
                                <form method="POST" action="{{ route('reservations.check-out', $r) }}" onsubmit="return confirm('Proses check-out sekarang?')">@csrf
                                    <button class="{{ $btn }} inline-flex items-center justify-center gap-1.5 bg-slate-800 text-white hover:bg-slate-900">
                                        <x-icon name="plane-takeoff" class="h-4 w-4" /> Check-out
                                    </button>
                                </form>
                            @endif
                        @endif

                        @if (in_array($r->status, [\App\Models\Reservation::STATUS_PENDING, \App\Models\Reservation::STATUS_CONFIRMED], true))
                            <details class="rounded-xl ring-1 ring-rose-200">
                                <summary class="cursor-pointer rounded-xl bg-rose-50 px-4 py-2.5 text-sm font-bold text-rose-700">Batalkan reservasi</summary>
                                <form method="POST" action="{{ route('reservations.cancel', $r) }}" class="space-y-3 p-4" onsubmit="return confirm('Yakin membatalkan reservasi ini?')">
                                    @csrf
                                    <label class="block text-sm font-semibold text-slate-700">Alasan *
                                        <input type="text" name="reason" required maxlength="200" class="{{ $inp }}" placeholder="mis. tamu membatalkan / double booking">
                                    </label>
                                    <label class="block text-sm font-semibold text-slate-700">Refund (Rp) <span class="font-normal text-slate-400">maks. {{ rupiah($r->paid_amount) }}</span>
                                        <input type="number" name="refund_amount" min="0" max="{{ $r->paid_amount }}" value="0" class="{{ $inp }}">
                                    </label>
                                    <button class="{{ $btn }} bg-rose-600 text-white hover:bg-rose-700">Batalkan</button>
                                </form>
                            </details>
                        @endif
                    </div>
                </section>
            @endif

            {{-- OTA --}}
            @unless ($r->isOtaChannel())
                <section class="{{ $card }}">
                    <h2 class="font-bold text-slate-900">🌐 Sinkron OTA (manual)</h2>
                    @if ($r->needsOtaBlock())
                        <p class="mt-2 text-xs text-rose-700">Tanggal ini belum diblokir di Agoda/Tiket.com. Blokir sekarang agar tidak terjadi double booking dari OTA.</p>
                        <form method="POST" action="{{ route('reservations.ota-block', $r) }}" class="mt-3">@csrf
                            <button class="{{ $btn }} bg-rose-600 text-white hover:bg-rose-700">Tandai sudah diblokir di OTA</button>
                        </form>
                    @elseif ($r->needsOtaUnblock())
                        <p class="mt-2 text-xs text-amber-700">Reservasi ini {{ strtolower($r->statusLabel()) }}. Buka kembali tanggalnya di OTA.</p>
                        <form method="POST" action="{{ route('reservations.ota-unblock', $r) }}" class="mt-3">@csrf
                            <button class="{{ $btn }} bg-amber-500 text-white hover:bg-amber-600">Tandai sudah dibuka di OTA</button>
                        </form>
                    @elseif ($r->ota_blocked_at)
                        <p class="mt-2 text-xs text-emerald-700">✓ Diblokir di OTA pada {{ $r->ota_blocked_at->translatedFormat('d M H:i') }}@if ($r->ota_unblocked_at); dibuka kembali {{ $r->ota_unblocked_at->translatedFormat('d M H:i') }}@endif.</p>
                    @else
                        <p class="mt-2 text-xs text-slate-400">Tidak ada tindakan OTA yang diperlukan.</p>
                    @endif
                </section>
            @endunless
        @endif
    </aside>
</div>
@endsection
