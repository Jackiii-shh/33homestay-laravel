@extends('layouts.public')

@php
    $name = config('homestay.name');
    $waGeneral = \App\Services\WhatsAppLink::general();
    $dp = config('homestay.dp_percentage');
    $dpHours = config('homestay.dp_deadline_hours');

    $roomPhotos = [
        'standard-room-ac' => 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?auto=format&fit=crop&w=800&q=80',
        'standard-room-kipas' => 'https://images.unsplash.com/photo-1598928506311-c55ded91a20c?auto=format&fit=crop&w=800&q=80',
        'mezanin' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=800&q=80',
        'tera-house' => 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?auto=format&fit=crop&w=800&q=80',
    ];
@endphp

@section('content')
{{-- ============================ NAVBAR ============================ --}}
<header x-data="{ menu: false, scrolled: false }"
        @scroll.window="scrolled = window.scrollY > 30"
        :class="scrolled ? 'bg-white/95 text-slate-900 shadow-md border-slate-200/80 backdrop-blur-md' : 'bg-forest-950/75 text-white border-white/10 backdrop-blur-sm'"
        class="fixed top-0 left-0 right-0 z-40 border-b transition-all duration-300">
    <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3 sm:px-6">
        <a href="#top" class="flex items-center gap-3">
            <img src="{{ asset('images/logo.jpg') }}" alt="{{ $name }}" class="h-10 w-10 rounded-full object-cover shadow-md ring-2 ring-white/30">
            <div class="flex flex-col">
                <span :class="scrolled ? 'text-slate-900' : 'text-white'" class="text-base font-extrabold tracking-tight sm:text-lg">{{ $name }}</span>
                <span :class="scrolled ? 'text-slate-500' : 'text-forest-200'" class="text-[10px] font-medium tracking-wider uppercase">Anyer · Serang</span>
            </div>
        </a>

        <nav class="hidden items-center gap-1 lg:flex">
            @foreach (['#ketersediaan' => 'Ketersediaan', '#kamar' => 'Kamar', '#tentang' => 'Tentang', '#cara-memesan' => 'Cara Pesan', '#ketentuan' => 'Ketentuan', '#faq' => 'FAQ', '#kontak' => 'Kontak'] as $href => $label)
                <a href="{{ $href }}"
                   :class="scrolled ? 'text-slate-600 hover:text-forest-800 hover:bg-slate-100/80' : 'text-white/85 hover:text-white hover:bg-white/10'"
                   class="rounded-lg px-3 py-2 text-sm font-medium transition-colors">{{ $label }}</a>
            @endforeach
        </nav>

        <div class="flex items-center gap-2">
            <a href="{{ $waGeneral }}" target="_blank" rel="noopener"
               class="hidden items-center justify-center gap-2 rounded-full bg-emerald-600 px-4 py-2 text-xs sm:text-sm font-bold text-white shadow transition hover:bg-emerald-700 sm:inline-flex">
                <x-icon name="message-circle" class="h-4 w-4" />
                <span>WhatsApp</span>
            </a>
            <button @click="menu = !menu"
                    :class="scrolled ? 'text-slate-700 hover:bg-slate-100' : 'text-white hover:bg-white/15'"
                    class="grid h-10 w-10 place-items-center rounded-xl transition lg:hidden"
                    aria-label="Toggle menu">
                <span x-show="!menu"><x-icon name="menu" class="h-5 w-5" /></span>
                <span x-show="menu" x-cloak><x-icon name="x" class="h-5 w-5" /></span>
            </button>
        </div>
    </div>

    {{-- Mobile Dropdown Menu --}}
    <div x-show="menu" x-cloak x-transition.opacity
         class="border-t border-slate-200 bg-white px-4 py-4 shadow-xl text-slate-800 lg:hidden">
        <div class="grid gap-1.5 text-sm font-semibold">
            @foreach (['#ketersediaan' => 'Cek Ketersediaan', '#kamar' => 'Pilihan Kamar', '#tentang' => 'Tentang Homestay', '#cara-memesan' => 'Cara Memesan', '#ketentuan' => 'Ketentuan Menginap', '#faq' => 'Pertanyaan Umum', '#kontak' => 'Lokasi & Kontak'] as $href => $label)
                <a @click="menu = false" href="{{ $href }}"
                   class="rounded-xl px-3.5 py-2.5 text-slate-700 hover:bg-forest-50 hover:text-forest-800 transition">{{ $label }}</a>
            @endforeach
            <div class="pt-2">
                <a href="{{ $waGeneral }}" target="_blank" rel="noopener"
                   class="flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 font-bold text-white shadow-md hover:bg-emerald-700 transition">
                    <x-icon name="message-circle" class="h-4 w-4" /> Chat WhatsApp Pengelola
                </a>
            </div>
        </div>
    </div>
</header>

<main id="top">
{{-- ============================ HERO ============================ --}}
<section class="relative min-h-[90vh] flex items-center justify-center overflow-hidden bg-forest-950 pt-24 pb-16 lg:py-32">
    {{-- Background image --}}
    <div class="absolute inset-0">
        <img src="https://images.unsplash.com/photo-1582719508461-905c673771fd?auto=format&fit=crop&w=1920&q=80"
             alt="33 Homestay Anyer" class="h-full w-full object-cover" loading="eager">
        <div class="hero-overlay absolute inset-0"></div>
    </div>

    <div class="relative mx-auto w-full max-w-6xl px-4 sm:px-6">
        <div class="max-w-2xl text-left">
            <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3.5 py-1.5 text-xs font-semibold tracking-wider text-white backdrop-blur-md">
                <x-icon name="trees" class="h-3.5 w-3.5 text-forest-300" />
                <span>KELUARGA · PASANGAN · GRUP KECIL</span>
            </div>
            <h1 class="font-serif text-3xl font-bold leading-tight text-white sm:text-5xl lg:text-6xl">
                Menginap tenang<br>di <span class="italic text-sand-300">{{ $name }}</span>
            </h1>
            <p class="mt-4 max-w-xl text-sm leading-relaxed text-white/85 sm:text-base lg:text-lg">
                Lihat tipe kamar, harga, dan ketersediaan secara langsung. Sudah cocok? Pesan lewat WhatsApp pengelola, tanpa ribet.
            </p>

            {{-- Action Buttons --}}
            <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center">
                <a href="#ketersediaan"
                   class="inline-flex w-full sm:w-auto items-center justify-center gap-2.5 rounded-full bg-white px-7 py-3.5 text-sm font-bold text-forest-950 shadow-lg hover:bg-warm-100 transition active:scale-[0.98]">
                    <x-icon name="calendar-search" class="h-4 w-4 text-forest-800" />
                    <span>Cek Ketersediaan</span>
                </a>
                <a href="{{ $waGeneral }}" target="_blank" rel="noopener"
                   class="inline-flex w-full sm:w-auto items-center justify-center gap-2.5 rounded-full bg-emerald-600 px-7 py-3.5 text-sm font-bold text-white shadow-lg hover:bg-emerald-700 transition active:scale-[0.98]">
                    <x-icon name="message-circle" class="h-4 w-4 text-white" />
                    <span>Pesan via WhatsApp</span>
                </a>
            </div>
        </div>

        {{-- Stats bar --}}
        <div class="mt-10 sm:mt-12 grid max-w-lg grid-cols-3 divide-x divide-white/20 rounded-2xl border border-white/20 bg-white/10 p-1 backdrop-blur-md">
            <div class="px-3 py-3 sm:py-4 text-center">
                <p class="font-serif text-xl sm:text-3xl font-bold text-white">{{ $types->count() }}</p>
                <p class="mt-0.5 text-[11px] sm:text-xs text-white/70">Tipe kamar</p>
            </div>
            <div class="px-3 py-3 sm:py-4 text-center">
                <p class="font-serif text-xl sm:text-3xl font-bold text-white">{{ config('homestay.checkin_time') }}</p>
                <p class="mt-0.5 text-[11px] sm:text-xs text-white/70">Check-in</p>
            </div>
            <div class="px-3 py-3 sm:py-4 text-center">
                <p class="font-serif text-xl sm:text-3xl font-bold text-white">{{ $dp }}%</p>
                <p class="mt-0.5 text-[11px] sm:text-xs text-white/70">DP mulai</p>
            </div>
        </div>
    </div>
</section>

{{-- ============================ INFO STRIP ============================ --}}
<section class="border-b border-slate-200/80 bg-white">
    <div class="mx-auto grid max-w-6xl divide-y sm:divide-y-0 sm:divide-x divide-slate-100 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['info', 'bg-forest-50 text-forest-700', 'Info saja', 'Website ini hanya menampilkan informasi kamar & ketersediaan.'],
            ['bot', 'bg-emerald-50 text-emerald-700', 'Asisten virtual', 'Tanyakan apapun ke asisten di pojok kanan bawah.'],
            ['message-circle', 'bg-emerald-50 text-emerald-700', 'Pesan via WA', 'Pengelola memandu pemesanan lewat WhatsApp Business.'],
            ['file-check', 'bg-forest-50 text-forest-700', 'Invoice resmi', 'Setiap pembayaran mendapat invoice PDF terverifikasi.'],
        ] as [$icon, $iconStyle, $title, $desc])
            <div class="flex items-start gap-4 p-5 sm:p-6">
                <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl {{ $iconStyle }} border border-current/15">
                    <x-icon name="{{ $icon }}" class="h-5 w-5" />
                </div>
                <div>
                    <p class="text-sm font-bold text-slate-900">{{ $title }}</p>
                    <p class="mt-1 text-xs leading-relaxed text-slate-500">{{ $desc }}</p>
                </div>
            </div>
        @endforeach
    </div>
</section>

{{-- ============================ CEK KETERSEDIAAN ============================ --}}
<section id="ketersediaan" class="scroll-mt-16 bg-warm-50 py-16 sm:py-20">
    <div class="mx-auto max-w-6xl px-4 sm:px-6">
        <div class="mx-auto max-w-2xl text-center">
            <p class="text-xs font-bold uppercase tracking-widest text-forest-700">Pesan Langsung Hari Ini</p>
            <h2 class="mt-2 font-serif text-2xl font-bold text-slate-900 sm:text-4xl">Cek Ketersediaan Kamar</h2>
            <p class="mt-2 text-sm sm:text-base text-slate-500">Pilih tanggal dan jumlah tamu untuk melihat kamar yang masih kosong.</p>
        </div>

        <form method="GET" action="{{ route('home') }}#ketersediaan"
              x-data="{ ci: @js($input['check_in'] ?? ''), co: @js($input['check_out'] ?? ''),
                        sync() { if (this.ci && (!this.co || this.co <= this.ci)) { const d = new Date(this.ci + 'T00:00:00'); d.setDate(d.getDate() + 1); const p = n => String(n).padStart(2, '0'); this.co = d.getFullYear() + '-' + p(d.getMonth() + 1) + '-' + p(d.getDate()); } } }"
              class="mx-auto mt-8 max-w-4xl overflow-hidden rounded-2xl bg-white shadow-lg ring-1 ring-slate-200">
            <div class="grid grid-cols-1 divide-y sm:divide-y-0 sm:divide-x divide-slate-100 sm:grid-cols-2 lg:grid-cols-5">
                <label class="flex flex-col bg-white px-5 py-3.5 text-sm">
                    <span class="mb-1 text-[11px] font-bold uppercase tracking-wider text-slate-400">Check-in</span>
                    <input type="date" name="check_in" x-model="ci" @change="sync()" min="{{ today()->toDateString() }}" required
                           class="w-full border-0 p-0 text-sm font-semibold text-slate-900 focus:ring-0">
                </label>
                <label class="flex flex-col bg-white px-5 py-3.5 text-sm">
                    <span class="mb-1 text-[11px] font-bold uppercase tracking-wider text-slate-400">Check-out</span>
                    <input type="date" name="check_out" x-model="co" :min="ci || '{{ today()->addDay()->toDateString() }}'" required
                           class="w-full border-0 p-0 text-sm font-semibold text-slate-900 focus:ring-0">
                </label>
                <label class="flex flex-col bg-white px-5 py-3.5 text-sm">
                    <span class="mb-1 text-[11px] font-bold uppercase tracking-wider text-slate-400">Jumlah Tamu</span>
                    <input type="number" name="guests" min="1" max="10" value="{{ $input['guests'] ?? 2 }}"
                           class="w-full border-0 p-0 text-sm font-semibold text-slate-900 focus:ring-0">
                </label>
                <label class="flex flex-col bg-white px-5 py-3.5 text-sm">
                    <span class="mb-1 text-[11px] font-bold uppercase tracking-wider text-slate-400">Tipe Kamar</span>
                    <select name="room_type" class="w-full border-0 p-0 text-sm font-semibold text-slate-900 focus:ring-0">
                        <option value="">Semua tipe</option>
                        @foreach ($types as $t)
                            <option value="{{ $t->id }}" @selected(($input['room_type'] ?? '') == $t->id)>{{ $t->name }}</option>
                        @endforeach
                    </select>
                </label>
                <div class="flex items-center justify-center bg-slate-50/80 p-3 sm:col-span-2 lg:col-span-1">
                    <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-forest-800 px-5 py-3 text-sm font-bold text-white shadow transition hover:bg-forest-900 active:scale-[0.98]">
                        <x-icon name="search" class="h-4 w-4" />
                        <span>Cek Kamar</span>
                    </button>
                </div>
            </div>
        </form>

        @if ($searchErrors)
            <div class="mx-auto mt-6 max-w-4xl rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
                <p class="font-semibold flex items-center gap-2"><x-icon name="alert-circle" class="h-4 w-4" /> Mohon periksa kembali isian Anda:</p>
                <ul class="ml-5 mt-1 list-disc">@foreach ($searchErrors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        @if ($results)
            @php
                $in = \Illuminate\Support\Carbon::parse($input['check_in']);
                $out = \Illuminate\Support\Carbon::parse($input['check_out']);
            @endphp
            <div class="mx-auto mt-8 max-w-4xl">
                <p class="mb-5 text-center text-sm text-slate-600">
                    Hasil untuk <span class="font-bold text-forest-800">{{ $in->translatedFormat('d M Y') }} – {{ $out->translatedFormat('d M Y') }}</span>
                    ({{ $results->first()['nights'] ?? 1 }} malam)
                </p>
                <div class="grid gap-5 md:grid-cols-2">
                    @foreach ($results as $row)
                        @php $ok = $row['available_units'] > 0 && $row['fits_guests']; @endphp
                        <div class="flex flex-col rounded-2xl bg-white p-6 shadow-sm ring-1 {{ $ok ? 'ring-forest-200' : 'ring-slate-200' }}">
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="font-serif text-lg font-bold text-slate-900">{{ $row['type']->name }}</h3>
                                @if ($row['available_units'] > 0)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">
                                        <x-icon name="check-circle" class="h-3 w-3" /> {{ $row['available_units'] }} unit tersedia
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2.5 py-1 text-xs font-bold text-rose-700">
                                        <x-icon name="x-circle" class="h-3 w-3" /> Penuh
                                    </span>
                                @endif
                            </div>
                            <p class="mt-1 flex items-center gap-1.5 text-xs text-slate-500">
                                <x-icon name="users" class="h-3.5 w-3.5 text-forest-600" /> Maks. {{ $row['type']->capacity }} orang
                            </p>
                            <p class="mt-4 font-serif text-2xl font-bold text-slate-900">{{ rupiah($row['total_price']) }}</p>
                            <p class="text-xs text-slate-500">{{ rupiah($row['price_per_night']) }} / malam × {{ $row['nights'] }} malam</p>

                            @unless ($row['fits_guests'])
                                <p class="mt-3 flex items-center gap-2 rounded-lg bg-amber-50 p-2.5 text-xs text-amber-800">
                                    <x-icon name="alert-triangle" class="h-4 w-4 flex-shrink-0" />
                                    <span>Kapasitas tidak cukup untuk {{ $input['guests'] }} tamu.</span>
                                </p>
                            @endunless

                            <div class="mt-auto pt-5">
                                @if ($ok)
                                    <a href="{{ $row['wa_url'] }}" target="_blank" rel="noopener"
                                       class="flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 py-3 text-sm font-bold text-white shadow transition hover:bg-emerald-700">
                                        <x-icon name="message-circle" class="h-4 w-4" />
                                        <span>Pesan via WhatsApp</span>
                                    </a>
                                @else
                                    <span class="flex w-full cursor-not-allowed items-center justify-center rounded-xl bg-slate-100 py-3 text-sm font-semibold text-slate-400">Tidak tersedia</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                @if (count($alternatives))
                    <div class="mt-6 rounded-2xl border border-sand-300 bg-sand-50 p-5">
                        <p class="flex items-center gap-2 text-sm font-bold text-sand-900">
                            <x-icon name="calendar-clock" class="h-4 w-4 text-sand-700" />
                            <span>Tanggal tersebut penuh. Coba tanggal terdekat ini:</span>
                        </p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($alternatives as $alt)
                                <a href="{{ route('home', array_merge($input, ['check_in' => $alt['check_in'], 'check_out' => $alt['check_out']])) }}#ketersediaan"
                                   class="rounded-full bg-white px-4 py-2 text-sm font-semibold text-sand-900 ring-1 ring-sand-300 transition hover:bg-sand-100">
                                    {{ \Illuminate\Support\Carbon::parse($alt['check_in'])->translatedFormat('d M') }} – {{ \Illuminate\Support\Carbon::parse($alt['check_out'])->translatedFormat('d M') }}
                                    <span class="text-xs font-normal text-slate-500">({{ $alt['units'] }} unit)</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <p class="mt-5 text-center text-xs text-slate-500">Informasi ketersediaan bersifat awal. Konfirmasi final dan pemesanan dilakukan pengelola melalui WhatsApp Business.</p>
            </div>
        @endif
    </div>
</section>

{{-- ============================ TENTANG ============================ --}}
<section id="tentang" class="scroll-mt-16 bg-white py-16 sm:py-20">
    <div class="mx-auto grid max-w-6xl items-center gap-10 px-4 sm:px-6 lg:grid-cols-2 lg:gap-16">
        <div>
            <p class="flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-forest-700">
                <span class="h-px w-8 bg-forest-400"></span> Tentang Kami
            </p>
            <h2 class="mt-3 font-serif text-3xl font-bold text-slate-900 sm:text-4xl">Hunian nyaman<br>di pesisir Anyer</h2>
            <p class="mt-4 leading-relaxed text-slate-600">
                {{ $name }} berlokasi di Bandulu, Kecamatan Anyar — dikelilingi keindahan pantai dan suasana alam yang menenangkan.
                Pilihan tepat untuk liburan keluarga, istirahat bersama pasangan, atau santai bersama grup kecil.
            </p>
            <div class="mt-8 grid grid-cols-3 gap-3 sm:gap-4">
                <div class="rounded-2xl border border-slate-100 bg-warm-50 p-4 text-center">
                    <p class="font-serif text-2xl sm:text-3xl font-bold text-forest-800">{{ $types->count() }}</p>
                    <p class="mt-0.5 text-xs text-slate-500">Tipe Kamar</p>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-warm-50 p-4 text-center">
                    <p class="font-serif text-2xl sm:text-3xl font-bold text-forest-800">{{ $types->sum(fn($t) => $t->rooms_count ?? $t->rooms()->count()) }}</p>
                    <p class="mt-0.5 text-xs text-slate-500">Total Unit</p>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-warm-50 p-4 text-center">
                    <p class="font-serif text-2xl sm:text-3xl font-bold text-forest-800">{{ config('homestay.checkin_time') }}</p>
                    <p class="mt-0.5 text-xs text-slate-500">Check-in</p>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3 sm:gap-4">
            <img src="https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=600&q=80"
                 alt="Eksterior villa" class="h-60 sm:h-72 w-full rounded-2xl object-cover shadow-md" loading="lazy">
            <img src="https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=600&q=80"
                 alt="Interior homestay" class="mt-6 sm:mt-8 h-60 sm:h-72 w-full rounded-2xl object-cover shadow-md" loading="lazy">
        </div>
    </div>
</section>

{{-- ============================ KAMAR ============================ --}}
<section id="kamar" class="scroll-mt-16 bg-warm-50 py-16 sm:py-20">
    <div class="mx-auto max-w-6xl px-4 sm:px-6">
        <div class="mx-auto max-w-2xl text-center">
            <p class="text-xs font-bold uppercase tracking-widest text-forest-700">Akomodasi</p>
            <h2 class="mt-2 font-serif text-3xl font-bold text-slate-900 sm:text-4xl">Pilihan Kamar</h2>
            <p class="mt-2 text-sm sm:text-base text-slate-500">Pilih kenyamanan terbaik untuk liburan Anda di Anyer.</p>
        </div>

        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($types as $t)
                @php
                    $photoUrl = $roomPhotos[$t->slug] ?? 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?auto=format&fit=crop&w=800&q=80';
                @endphp
                <article class="flex flex-col overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/80 transition hover:shadow-lg">
                    <div class="relative h-48 w-full overflow-hidden bg-slate-100">
                        <img src="{{ $photoUrl }}" alt="{{ $t->name }}"
                             onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1566665797739-1674de7a421a?auto=format&fit=crop&w=800&q=80';"
                             class="h-full w-full object-cover transition duration-500 hover:scale-105" loading="lazy">
                        <span class="absolute left-3 top-3 inline-flex items-center gap-1.5 rounded-full bg-white/95 px-3 py-1 text-xs font-bold text-slate-800 shadow backdrop-blur">
                            <x-icon name="users" class="h-3.5 w-3.5 text-forest-700" />
                            <span>Maks. {{ $t->capacity }} orang</span>
                        </span>
                    </div>

                    <div class="flex flex-1 flex-col p-5">
                        <h3 class="font-serif text-lg font-bold text-slate-900">{{ $t->name }}</h3>
                        <p class="mt-1.5 text-xs leading-relaxed text-slate-500 line-clamp-2">{{ $t->description }}</p>

                        @if (!empty($t->facilities))
                            <ul class="mt-3 flex flex-wrap gap-1.5">
                                @foreach ($t->facilities as $f)
                                    <li class="rounded-full bg-forest-50 px-2.5 py-0.5 text-[11px] font-semibold text-forest-800">{{ $f }}</li>
                                @endforeach
                            </ul>
                        @endif

                        <div class="mt-auto pt-5 border-t border-slate-100">
                            <p class="text-[11px] text-slate-400">Mulai dari</p>
                            <p class="font-serif text-xl font-bold text-slate-900">{{ rupiah($t->price_per_night) }}<span class="text-xs font-normal text-slate-400"> /malam</span></p>

                            <a href="{{ \App\Services\WhatsAppLink::inquiry(['room_type' => $t->name]) }}" target="_blank" rel="noopener"
                               class="mt-3 flex w-full items-center justify-center gap-2 rounded-xl bg-forest-800 py-2.5 text-xs sm:text-sm font-bold text-white shadow transition hover:bg-forest-900">
                                <x-icon name="message-circle" class="h-4 w-4" />
                                <span>Tanya via WhatsApp</span>
                            </a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================ CARA MEMESAN ============================ --}}
<section id="cara-memesan" class="scroll-mt-16 bg-forest-950 py-16 text-white sm:py-20">
    <div class="mx-auto max-w-6xl px-4 sm:px-6">
        <div class="mx-auto max-w-2xl text-center">
            <p class="text-xs font-bold uppercase tracking-widest text-forest-300">Mudah & Cepat</p>
            <h2 class="mt-2 font-serif text-3xl font-bold sm:text-4xl">Cara Memesan</h2>
            <p class="mt-2 text-sm sm:text-base text-forest-200">Pemesanan dilakukan langsung via WhatsApp Business pengelola.</p>
        </div>

        <ol class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ([
                ['search', 'Cek kamar', 'Gunakan formulir Cek Ketersediaan atau tanyakan ke asisten virtual.'],
                ['message-circle', 'Hubungi pengelola', 'Klik tombol WhatsApp. Pesan pemesanan sudah terisi otomatis.'],
                ['calendar-check', 'Konfirmasi', 'Pengelola memastikan kamar kosong dan mengunci tanggal untuk Anda.'],
                ['wallet', 'Bayar DP '.$dp.'%', 'Transfer DP maksimal '.$dpHours.' jam setelah dikonfirmasi agar tanggal tetap terkunci.'],
                ['file-check', 'Terima invoice', 'Invoice bernomor unik dikirim via WhatsApp dan dapat diverifikasi keasliannya.'],
                ['key-round', 'Check-in', 'Datang pada hari-H, lunasi sisa pembayaran di lokasi, lalu menginap dengan nyaman.'],
            ] as $i => [$icon, $title, $desc])
                <li class="rounded-2xl border border-white/10 bg-white/5 p-5 sm:p-6 backdrop-blur">
                    <div class="flex items-center gap-3">
                        <span class="grid h-8 w-8 place-items-center rounded-xl bg-emerald-500/20 text-xs font-extrabold text-emerald-300">{{ $i + 1 }}</span>
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/10 text-forest-200">
                            <x-icon name="{{ $icon }}" class="h-4 w-4" />
                        </div>
                    </div>
                    <h3 class="mt-4 font-bold text-white text-base">{{ $title }}</h3>
                    <p class="mt-1.5 text-xs leading-relaxed text-forest-200">{{ $desc }}</p>
                </li>
            @endforeach
        </ol>

        <div class="mt-10 text-center">
            <a href="{{ $waGeneral }}" target="_blank" rel="noopener"
               class="inline-flex items-center justify-center gap-2.5 rounded-full bg-emerald-600 px-8 py-3.5 text-sm sm:text-base font-bold text-white shadow-lg transition hover:bg-emerald-700">
                <x-icon name="message-circle" class="h-5 w-5" />
                <span>Mulai Pesan via WhatsApp</span>
            </a>
        </div>
    </div>
</section>

{{-- ============================ KEUNGGULAN ============================ --}}
<section class="bg-white py-16 sm:py-20">
    <div class="mx-auto max-w-6xl px-4 sm:px-6">
        <div class="mx-auto max-w-2xl text-center">
            <p class="text-xs font-bold uppercase tracking-widest text-forest-700">Mengapa Kami</p>
            <h2 class="mt-2 font-serif text-3xl font-bold text-slate-900 sm:text-4xl">Pengalaman Tanpa Khawatir</h2>
        </div>
        <div class="mx-auto mt-10 grid max-w-4xl gap-6 sm:grid-cols-3">
            @foreach ([
                ['calendar-days', 'bg-forest-50 text-forest-700', 'Jadwal selalu terbarui', 'Seluruh pengelola melihat satu kalender yang sama sehingga kamar yang sudah dipesan tidak ditawarkan lagi.'],
                ['shield-check', 'bg-emerald-50 text-emerald-700', 'Anti double booking', 'Sistem menolak otomatis bila tanggal kamar bertabrakan dengan pesanan lain.'],
                ['receipt', 'bg-sand-50 text-sand-800', 'Bukti pembayaran jelas', 'Invoice PDF digital untuk DP maupun pelunasan, lengkap dengan tautan verifikasi keaslian.'],
            ] as [$icon, $iconStyle, $title, $desc])
                <div class="rounded-2xl border border-slate-100 bg-warm-50 p-6 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl {{ $iconStyle }}">
                        <x-icon name="{{ $icon }}" class="h-7 w-7" />
                    </div>
                    <h3 class="mt-4 font-serif text-lg font-bold text-slate-900">{{ $title }}</h3>
                    <p class="mt-2 text-xs sm:text-sm leading-relaxed text-slate-500">{{ $desc }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================ KETENTUAN ============================ --}}
<section id="ketentuan" class="scroll-mt-16 bg-warm-50 py-16 sm:py-20">
    <div class="mx-auto max-w-4xl px-4 sm:px-6">
        <div class="text-center">
            <p class="text-xs font-bold uppercase tracking-widest text-forest-700">Informasi Penting</p>
            <h2 class="mt-2 font-serif text-3xl font-bold text-slate-900 sm:text-4xl">Ketentuan Menginap</h2>
        </div>
        <div class="mt-8 grid gap-4 sm:grid-cols-2">
            @foreach ([
                ['clock', 'Check-in & check-out', 'Check-in mulai pukul '.config('homestay.checkin_time').' WIB. Check-out maksimal pukul '.config('homestay.checkout_time').' WIB.'],
                ['credit-card', 'Pembayaran', 'DP minimal '.$dp.'% dari total, maksimal '.$dpHours.' jam setelah pengelola mengonfirmasi. Pelunasan dilakukan saat check-in.'],
                ['timer', 'Batas waktu DP', 'Bila DP belum masuk sampai batas waktu, pemesanan dibatalkan otomatis dan tanggal dibuka kembali untuk tamu lain.'],
                ['user-x', 'Tidak datang (no-show)', 'Bila tamu tidak datang melewati batas check-in tanpa kabar, DP dinyatakan hangus dan kamar dibuka kembali.'],
                ['calendar-range', 'Perubahan jadwal', 'Ubah tanggal, perpanjang inap, atau pembatalan diajukan melalui WhatsApp dan bergantung pada ketersediaan kamar.'],
                ['users', 'Kapasitas', 'Jumlah tamu tidak boleh melebihi kapasitas tipe kamar yang dipesan.'],
            ] as [$icon, $title, $desc])
                <div class="flex gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100">
                    <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-forest-50 text-forest-700">
                        <x-icon name="{{ $icon }}" class="h-5 w-5" />
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm sm:text-base">{{ $title }}</h3>
                        <p class="mt-1 text-xs leading-relaxed text-slate-500">{{ $desc }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================ FAQ ============================ --}}
<section id="faq" class="scroll-mt-16 bg-white py-16 sm:py-20">
    <div class="mx-auto max-w-3xl px-4 sm:px-6">
        <div class="text-center">
            <p class="text-xs font-bold uppercase tracking-widest text-forest-700">Bantuan</p>
            <h2 class="mt-2 font-serif text-3xl font-bold text-slate-900 sm:text-4xl">Pertanyaan Umum</h2>
        </div>
        <div x-data="{ open: 0 }" class="mt-8 divide-y divide-slate-100 rounded-2xl bg-warm-50 ring-1 ring-slate-200/80">
            @foreach ([
                ['Apakah saya bisa memesan langsung di website ini?', 'Tidak. Website ini hanya menampilkan informasi kamar dan ketersediaan. Pemesanan dilakukan melalui WhatsApp Business pengelola agar ketersediaan dikonfirmasi langsung.'],
                ['Apakah informasi ketersediaan di sini akurat?', 'Ketersediaan diperbarui oleh pengelola dari kalender terpusat. Karena bersifat informasi awal, konfirmasi final tetap dilakukan pengelola saat Anda menghubungi via WhatsApp.'],
                ['Bagaimana cara membayar?', 'Bayar DP '.$dp.'% via transfer setelah pengelola mengonfirmasi, lalu pelunasan saat check-in. Setiap pembayaran mendapat invoice PDF.'],
                ['Bagaimana memastikan invoice asli?', 'Setiap invoice memiliki nomor unik dan tautan verifikasi. Buka tautan tersebut untuk mencocokkan nominal dengan catatan sistem.'],
                ['Bisakah saya bertanya dulu sebelum memesan?', 'Tentu. Gunakan asisten virtual di pojok kanan bawah untuk bertanya soal kamar, harga, dan ketersediaan. Saat Anda siap, asisten akan mengarahkan ke WhatsApp pengelola.'],
            ] as $i => [$q, $a])
                <div class="px-5">
                    <button type="button" @click="open = open === {{ $i + 1 }} ? 0 : {{ $i + 1 }}"
                            class="flex w-full items-center justify-between gap-4 py-4 text-left">
                        <span class="font-bold text-slate-800 text-sm sm:text-base">{{ $q }}</span>
                        <span class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-lg bg-forest-50 text-forest-700">
                            <span x-show="open !== {{ $i + 1 }}"><x-icon name="plus" class="h-4 w-4" /></span>
                            <span x-show="open === {{ $i + 1 }}" x-cloak><x-icon name="minus" class="h-4 w-4" /></span>
                        </span>
                    </button>
                    <div x-show="open === {{ $i + 1 }}" x-cloak>
                        <p class="pb-4 text-xs sm:text-sm leading-relaxed text-slate-600">{{ $a }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================ KONTAK ============================ --}}
<section id="kontak" class="scroll-mt-16 bg-forest-950 py-16 text-white sm:py-20">
    <div class="mx-auto grid max-w-6xl gap-10 px-4 sm:px-6 md:grid-cols-2">
        <div>
            <p class="flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-forest-300">
                <span class="h-px w-8 bg-forest-500"></span> Hubungi Kami
            </p>
            <h2 class="mt-3 font-serif text-3xl font-bold sm:text-4xl">Lokasi & Kontak</h2>
            <p class="mt-4 text-sm leading-relaxed text-forest-200">{{ config('homestay.address') }}</p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ config('homestay.maps_url') }}" target="_blank" rel="noopener"
                   class="inline-flex items-center justify-center gap-2 rounded-full bg-white px-5 py-2.5 text-xs sm:text-sm font-bold text-forest-950 shadow transition hover:bg-warm-100">
                    <x-icon name="map-pin" class="h-4 w-4 text-forest-800" />
                    <span>Buka Google Maps</span>
                </a>
                <a href="{{ $waGeneral }}" target="_blank" rel="noopener"
                   class="inline-flex items-center justify-center gap-2 rounded-full bg-emerald-600 px-5 py-2.5 text-xs sm:text-sm font-bold text-white shadow transition hover:bg-emerald-700">
                    <x-icon name="message-circle" class="h-4 w-4" />
                    <span>WhatsApp</span>
                </a>
            </div>
        </div>

        <div class="rounded-2xl border border-white/10 bg-white/5 p-6 backdrop-blur">
            <p class="font-bold text-white text-base">Media Sosial</p>
            <ul class="mt-4 space-y-3 text-sm">
                <li class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 text-white">
                        <x-icon name="instagram" class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-xs text-forest-300">Instagram</p>
                        <a class="font-semibold text-white hover:underline" target="_blank" rel="noopener"
                           href="https://instagram.com/{{ config('homestay.instagram') }}">{{ '@'.config('homestay.instagram') }}</a>
                    </div>
                </li>
                <li class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 text-white">
                        <x-icon name="music-2" class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-xs text-forest-300">TikTok</p>
                        <a class="font-semibold text-white hover:underline" target="_blank" rel="noopener"
                           href="https://www.tiktok.com/{{ '@'.config('homestay.tiktok') }}">{{ '@'.config('homestay.tiktok') }}</a>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</section>
</main>

<footer class="border-t border-forest-900 bg-forest-950 py-10 text-center text-xs sm:text-sm text-forest-400">
    <div class="mx-auto flex flex-col items-center justify-center">
        <img src="{{ asset('images/logo.jpg') }}" alt="{{ $name }}" class="h-14 w-14 rounded-full object-cover shadow-lg ring-2 ring-white/20 mb-3">
        <p class="font-bold text-white text-base">{{ $name }}</p>
        <p class="mt-1">© {{ now()->year }} {{ $name }}. Pemesanan melalui WhatsApp Business.</p>
        <p class="mt-2"><a href="{{ route('login') }}" class="text-forest-400 hover:text-sand-300 underline transition">Login Staf & Owner</a></p>
    </div>
</footer>
@endsection
