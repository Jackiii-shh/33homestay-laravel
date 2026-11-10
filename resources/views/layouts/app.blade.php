<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · {{ config('homestay.name') }}</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}">
    <script src="https://cdn.tailwindcss.com/3.4.17?plugins=forms"></script>
    <script>
        tailwind.config = {
            theme: { extend: {
                colors: {
                    brand: { 50:'#f6f7f4',100:'#e8eae2',200:'#d2d6c6',300:'#b4baa2',400:'#95a07f',500:'#788663',600:'#5e6b4e',700:'#4a5540',800:'#3d4636',900:'#2f3529',950:'#1a1e17' },
                    forest: { 50:'#f2f7f2',100:'#e0ece0',200:'#c3d9c3',300:'#97bc97',400:'#6a9a6a',500:'#477e47',600:'#356535',700:'#2c512c',800:'#264126',900:'#1f3520',950:'#0e1d0f' },
                    warm: { 50:'#fdfcfa',100:'#f9f6f0',200:'#f2ecdf' },
                },
                fontFamily: { sans: ['"Plus Jakarta Sans"','ui-sans-serif','system-ui','sans-serif'] }
            } }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <style>[x-cloak]{display:none!important}</style>
    @stack('head')
</head>
@php
    $user = auth()->user();
    $nav = $user->isOwner()
        ? [
            ['owner.dashboard', 'Dashboard Owner', 'layout-dashboard', 'owner.dashboard'],
            ['calendar', 'Kalender Ketersediaan', 'calendar-days', 'calendar'],
            ['reservations.index', 'Daftar Reservasi', 'clipboard-list', 'reservations.index'],
            ['owner.payments', 'Laporan Pembayaran', 'credit-card', 'owner.payments'],
            ['owner.audit', 'Log Aktivitas', 'shield', 'owner.audit'],
            ['owner.room-types.index', 'Tipe & Harga Kamar', 'tag', 'owner.room-types.*'],
            ['owner.rooms.index', 'Unit Kamar', 'door-open', 'owner.rooms.*'],
            ['owner.users.index', 'Pengguna', 'users', 'owner.users.*'],
        ]
        : [
            ['staff.dashboard', 'Dashboard', 'layout-dashboard', 'staff.dashboard'],
            ['reservations.create', 'Reservasi Baru', 'plus-circle', 'reservations.create'],
            ['reservations.index', 'Daftar Reservasi', 'clipboard-list', 'reservations.index'],
            ['calendar', 'Kalender Ketersediaan', 'calendar-days', 'calendar'],
            ['rooms.index', 'Status Kamar', 'door-open', 'rooms.*'],
        ];
@endphp
<body class="h-full font-sans text-slate-700 antialiased bg-warm-50" x-data="{ side: false }">
    <div class="flex min-h-full">
        {{-- Sidebar overlay (mobile) --}}
        <div x-show="side" x-cloak @click="side = false"
             x-transition.opacity
             class="fixed inset-0 z-30 bg-slate-900/60 backdrop-blur-sm lg:hidden"></div>

        {{-- Sidebar --}}
        <aside :class="side ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
               class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col bg-forest-950 text-white shadow-2xl transition-transform duration-200 ease-in-out lg:static lg:flex-shrink-0 lg:shadow-none">
            <div class="flex items-center justify-between border-b border-white/10 px-5 py-4">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <img src="{{ asset('images/logo.jpg') }}" alt="{{ config('homestay.name') }}" class="h-10 w-10 rounded-full object-cover ring-2 ring-white/20">
                    <div>
                        <span class="block text-base font-extrabold leading-tight tracking-tight">{{ config('homestay.name') }}</span>
                        <span class="block text-[11px] font-medium text-emerald-400">{{ $user->roleLabel() }}</span>
                    </div>
                </a>
                <button @click="side = false" class="rounded-lg p-1.5 text-white/70 hover:bg-white/10 lg:hidden" aria-label="Tutup menu">
                    <x-icon name="x" class="h-5 w-5" />
                </button>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-3 text-sm font-medium">
                @foreach ($nav as [$route, $label, $icon, $pattern])
                    @php $active = request()->routeIs($pattern); @endphp
                    <a href="{{ route($route) }}"
                       class="flex items-center gap-3 rounded-xl px-3 py-2.5 transition {{ $active ? 'bg-forest-800 text-white font-bold shadow-sm' : 'text-forest-200 hover:bg-white/10 hover:text-white' }}">
                        <x-icon name="{{ $icon }}" class="h-4 w-4 {{ $active ? 'text-sand-300' : 'text-forest-400' }}" />
                        <span>{{ $label }}</span>
                    </a>
                @endforeach
            </nav>

            <div class="space-y-1 border-t border-white/10 p-3 text-sm font-medium">
                <a href="{{ route('home') }}" target="_blank" class="flex items-center gap-3 rounded-xl px-3 py-2 text-forest-200 hover:bg-white/10 hover:text-white transition">
                    <x-icon name="globe" class="h-4 w-4 text-forest-400" />
                    <span>Lihat Website</span>
                </a>
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-forest-200 hover:bg-white/10 hover:text-white transition">
                    <x-icon name="user" class="h-4 w-4 text-forest-400" />
                    <span>Profil & Sandi</span>
                </a>
                <form method="POST" action="{{ route('logout') }}">@csrf
                    <button class="flex w-full items-center gap-3 rounded-xl px-3 py-2 text-left text-rose-300 hover:bg-rose-500/20 hover:text-rose-200 transition">
                        <x-icon name="log-out" class="h-4 w-4 text-rose-400" />
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </aside>

        {{-- Main Content --}}
        <div class="flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-20 flex items-center justify-between gap-3 border-b border-slate-200/80 bg-white/95 px-4 py-3 backdrop-blur-md sm:px-6">
                <div class="flex items-center gap-3">
                    <button @click="side = true" class="grid h-10 w-10 place-items-center rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 lg:hidden" aria-label="Buka Menu">
                        <x-icon name="menu" class="h-5 w-5" />
                    </button>
                    <h1 class="text-base sm:text-lg font-bold text-slate-900 truncate">@yield('heading', 'Dashboard')</h1>
                </div>
                <div class="flex items-center gap-2 sm:gap-3 text-xs sm:text-sm">
                    <span class="hidden text-slate-500 md:inline">{{ now()->translatedFormat('l, d F Y') }}</span>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-forest-50 border border-forest-100 px-3 py-1 font-semibold text-forest-800">
                        <x-icon name="user" class="h-3.5 w-3.5" />
                        <span class="truncate max-w-[120px] sm:max-w-none">{{ $user->name }}</span>
                    </span>
                </div>
            </header>

            <main class="flex-1 px-3 py-4 sm:px-6 sm:py-6">
                @include('partials.flash')
                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
