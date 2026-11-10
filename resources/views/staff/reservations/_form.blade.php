@php
    $editing = isset($reservation);
    $locked = $editing && $reservation->status === \App\Models\Reservation::STATUS_CHECKED_IN;
    $inp = 'mt-1 w-full rounded-xl border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500 disabled:bg-slate-100 disabled:text-slate-500';
    $ci = old('check_in', $editing ? $reservation->check_in->toDateString() : ($prefill['check_in'] ?? today()->toDateString()));
    $co = old('check_out', $editing ? $reservation->check_out->toDateString() : ($prefill['check_out'] ?? today()->addDay()->toDateString()));
    $guests = old('guest_count', $editing ? $reservation->guest_count : ($prefill['guest_count'] ?? 2));
    $roomId = old('room_id', $editing ? $reservation->room_id : ($prefill['room_id'] ?? ''));
@endphp

<form method="POST" action="{{ $editing ? route('reservations.update', $reservation) : route('reservations.store') }}"
      x-data="roomPicker({
          ci: @js($ci), co: @js($co), guests: @js((int) $guests), room: @js((string) $roomId),
          ignore: @js($editing ? $reservation->id : null), locked: @js($locked), url: @js(route('staff.api.rooms')),
          fallback: @js($rooms->map(fn ($r) => ['id' => $r->id, 'label' => $r->code.' - '.$r->type->name, 'total' => null])->values())
      })"
      class="grid gap-6 lg:grid-cols-3">
    @csrf
    @if ($editing) @method('PUT') @endif

    <div class="space-y-5 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 lg:col-span-2">
        <h2 class="font-bold text-slate-900">Data tamu</h2>
        <div class="grid gap-4 sm:grid-cols-2">
            <label class="block text-sm font-semibold text-slate-700 sm:col-span-2">Nama tamu *
                <input type="text" name="guest_name" value="{{ old('guest_name', $reservation->guest_name ?? '') }}" required maxlength="100" class="{{ $inp }}">
            </label>
            <label class="block text-sm font-semibold text-slate-700">Asal kota
                <input type="text" name="guest_city" value="{{ old('guest_city', $reservation->guest_city ?? '') }}" maxlength="80" class="{{ $inp }}">
            </label>
            <label class="block text-sm font-semibold text-slate-700">No. HP / WhatsApp
                <input type="text" name="guest_phone" value="{{ old('guest_phone', $reservation->guest_phone ?? '') }}" maxlength="25" placeholder="08xxxxxxxxxx" class="{{ $inp }}">
                <span class="mt-1 block text-xs font-normal text-slate-400">Untuk mengirim invoice via WhatsApp. Jangan isi data identitas sensitif (KTP, dll).</span>
            </label>
        </div>

        <h2 class="pt-2 font-bold text-slate-900">Jadwal & kamar</h2>
        <div class="grid gap-4 sm:grid-cols-3">
            <label class="block text-sm font-semibold text-slate-700">Check-in *
                <input type="date" name="check_in" x-model="ci" @change="syncOut()" required {{ $editing ? '' : 'min='.today()->toDateString() }} @if ($locked) readonly @endif class="{{ $inp }}">
            </label>
            <label class="block text-sm font-semibold text-slate-700">Check-out *
                <input type="date" name="check_out" x-model="co" @change="load()" :min="ci" required class="{{ $inp }}">
                @if ($locked)<span class="mt-1 block text-xs font-normal text-slate-400">Tamu sudah check-in: hanya check-out yang dapat diubah (perpanjang/persingkat).</span>@endif
            </label>
            <label class="block text-sm font-semibold text-slate-700">Jumlah tamu *
                <input type="number" name="guest_count" x-model="guests" @change="load()" min="1" max="10" required class="{{ $inp }}">
            </label>
        </div>

        @if ($locked)
            <p class="rounded-xl bg-slate-50 p-3 text-sm">Kamar: <strong>{{ $reservation->room->code }}</strong> ({{ $reservation->room->type->name }})</p>
        @else
            <label class="block text-sm font-semibold text-slate-700">Kamar tersedia *
                <select name="room_id" x-model="room" required class="{{ $inp }}">
                    <option value="">— pilih kamar yang tersedia —</option>
                    <template x-for="o in options" :key="o.id">
                        <option :value="o.id" x-text="o.label" :selected="String(o.id) === String(room)"></option>
                    </template>
                </select>
                <span class="mt-1 block text-xs font-normal" :class="info ? 'text-amber-700' : 'text-slate-400'"
                      x-text="loading ? 'Memeriksa ketersediaan…' : (info || (options.length ? options.length + ' unit tersedia untuk tanggal ini.' : 'Tidak ada unit tersedia pada tanggal ini.'))"></span>
            </label>
        @endif

        @unless ($editing)
            <label class="block text-sm font-semibold text-slate-700">Kanal pemesanan *
                <select name="channel" required class="{{ $inp }}">
                    @foreach (\App\Models\Reservation::CHANNELS as $key => $label)
                        <option value="{{ $key }}" @selected(old('channel', 'whatsapp') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <span class="mt-1 block text-xs font-normal text-slate-400">Pesanan dari OTA langsung berstatus Terkonfirmasi (dibayar via OTA). Pesanan WhatsApp/walk-in menunggu DP.</span>
            </label>
        @endunless

        <label class="block text-sm font-semibold text-slate-700">Catatan
            <textarea name="notes" rows="2" maxlength="500" class="{{ $inp }}">{{ old('notes', $reservation->notes ?? '') }}</textarea>
        </label>
    </div>

    <aside class="space-y-4">
        <div class="rounded-2xl bg-brand-900 p-6 text-white shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-brand-200">Ringkasan</p>
            <p class="mt-3 text-sm text-brand-100"><span x-text="nights || '-'"></span> malam</p>
            <p class="mt-1 text-3xl font-extrabold" x-text="total !== null ? rp(total) : (room ? 'Dihitung saat disimpan' : '—')"></p>
            @unless ($editing)
                <p class="mt-3 text-xs text-brand-200">Setelah disimpan, tanggal kamar langsung <strong>terblokir</strong> di kalender. DP minimal {{ config('homestay.dp_percentage') }}% dalam {{ config('homestay.dp_deadline_hours') }} jam, bila terlambat reservasi batal otomatis.</p>
            @endunless
        </div>
        <button type="submit" class="w-full rounded-xl bg-brand-600 py-3 font-bold text-white hover:bg-brand-700">{{ $editing ? 'Simpan perubahan' : 'Simpan reservasi' }}</button>
        <a href="{{ $editing ? route('reservations.show', $reservation) : route('staff.dashboard') }}" class="block text-center text-sm text-slate-500 hover:text-slate-700">Batal</a>
        <p class="rounded-xl bg-amber-50 p-3 text-xs text-amber-800 flex items-start gap-2"><x-icon name="shield-check" class="h-4 w-4 text-amber-700 flex-shrink-0 mt-0.5" /> <span>Sistem memeriksa bentrok tanggal sekali lagi saat disimpan. Bila kamar sudah dipesan, penyimpanan ditolak dan alternatif ditampilkan.</span></p>
    </aside>
</form>

@push('scripts')
<script>
function roomPicker(cfg) {
    return {
        ci: cfg.ci, co: cfg.co, guests: cfg.guests, room: cfg.room, locked: cfg.locked,
        options: cfg.fallback, nights: 0, loading: false, info: '',
        get total() {
            const o = this.options.find(x => String(x.id) === String(this.room));
            return o && o.total !== null && o.total !== undefined ? o.total : null;
        },
        rp(n) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(n); },
        init() { if (!this.locked && this.ci && this.co) this.load(); else if (this.ci && this.co) this.calcNights(); },
        calcNights() {
            const a = new Date(this.ci + 'T00:00:00'), b = new Date(this.co + 'T00:00:00');
            this.nights = Math.max(0, Math.round((b - a) / 86400000));
        },
        syncOut() {
            if (this.ci && (!this.co || this.co <= this.ci)) {
                const d = new Date(this.ci + 'T00:00:00'); d.setDate(d.getDate() + 1);
                const p = n => String(n).padStart(2, '0');
                this.co = d.getFullYear() + '-' + p(d.getMonth() + 1) + '-' + p(d.getDate());
            }
            this.load();
        },
        async load() {
            this.calcNights();
            if (this.locked || !this.ci || !this.co || this.co <= this.ci) return;
            this.loading = true;
            try {
                const q = new URLSearchParams({ check_in: this.ci, check_out: this.co, guests: this.guests || '' });
                if (cfg.ignore) q.set('ignore', cfg.ignore);
                const res = await fetch(cfg.url + '?' + q.toString(), { headers: { 'Accept': 'application/json' } });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const data = await res.json();
                this.nights = data.nights; this.options = data.rooms;
                if (this.room && !this.options.find(o => String(o.id) === String(this.room))) {
                    this.info = 'Kamar yang dipilih sebelumnya tidak tersedia pada tanggal/jumlah tamu ini.'; this.room = '';
                } else { this.info = ''; }
            } catch (e) { this.info = 'Gagal memeriksa ketersediaan. Muat ulang halaman.'; }
            this.loading = false;
        }
    };
}
</script>
@endpush
