<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Login Staf & Owner · {{ config('homestay.name') }}</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}">
    <script src="https://cdn.tailwindcss.com/3.4.17?plugins=forms"></script>
    <script>
        tailwind.config = {
            theme: { extend: {
                colors: {
                    forest: { 50:'#f2f7f2',100:'#e0ece0',200:'#c3d9c3',300:'#97bc97',400:'#6a9a6a',500:'#477e47',600:'#356535',700:'#2c512c',800:'#264126',900:'#1f3520',950:'#0e1d0f' },
                    warm:   { 50:'#fdfcfa',100:'#f9f6f0',200:'#f2ecdf' },
                    sand:   { 300:'#d6c7a5' },
                },
                fontFamily: {
                    sans:  ['"Plus Jakarta Sans"','ui-sans-serif','system-ui','sans-serif'],
                    serif: ['"Playfair Display"','Georgia','serif'],
                }
            } }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="bg-warm-50 font-sans antialiased text-slate-800">
    <div class="grid min-h-screen lg:grid-cols-2">
        {{-- Left decorative panel --}}
        <div class="relative hidden lg:block overflow-hidden bg-forest-950">
            <img src="https://images.unsplash.com/photo-1582719508461-905c673771fd?auto=format&fit=crop&w=1200&q=80"
                 alt="Villa Anyer" class="h-full w-full object-cover opacity-60">
            <div class="absolute inset-0 bg-gradient-to-t from-forest-950 via-forest-950/60 to-transparent"></div>
            <div class="absolute inset-0 flex flex-col justify-end p-12 text-white">
                <div class="max-w-md">
                    <img src="{{ asset('images/logo.jpg') }}" alt="{{ config('homestay.name') }}" class="h-16 w-16 rounded-full object-cover shadow-lg ring-2 ring-white/30">
                    <h2 class="mt-4 font-serif text-3xl font-bold text-white">{{ config('homestay.name') }}</h2>
                    <p class="mt-2 text-sm leading-relaxed text-forest-200">Portal operasional pengelola & dashboard pemilik villa. Kelola ketersediaan, reservasi, dan pelaporan keuangan real-time.</p>
                </div>
            </div>
        </div>

        {{-- Right login form --}}
        <div class="flex items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
            <div class="w-full max-w-sm sm:max-w-md">
                <div class="mb-8 text-center lg:text-left">
                    <div class="mb-4 flex justify-center lg:justify-start">
                        <img src="{{ asset('images/logo.jpg') }}" alt="{{ config('homestay.name') }}" class="h-14 w-14 rounded-full object-cover shadow-md ring-2 ring-forest-700/20">
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Masuk Sistem</h1>
                    <p class="mt-1.5 text-xs sm:text-sm text-slate-500">Akses akun Pengelola & Owner {{ config('homestay.name') }}</p>
                </div>

                <form method="POST" action="{{ url('/login') }}" class="space-y-4 rounded-2xl bg-white p-6 sm:p-8 shadow-sm ring-1 ring-slate-200/80">
                    @csrf
                    @if ($errors->any())
                        <div class="flex items-center gap-2 rounded-xl bg-rose-50 p-3 text-xs sm:text-sm text-rose-700">
                            <x-icon name="alert-circle" class="h-4 w-4 flex-shrink-0" />
                            <span>{{ $errors->first() }}</span>
                        </div>
                    @endif

                    <div>
                        <label class="block text-xs sm:text-sm font-bold text-slate-700">Alamat Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                               placeholder="email@example.com"
                               class="mt-1.5 w-full rounded-xl border-slate-200 bg-slate-50 text-xs sm:text-sm focus:border-forest-600 focus:bg-white focus:ring-forest-600">
                    </div>

                    <div>
                        <label class="block text-xs sm:text-sm font-bold text-slate-700">Kata Sandi</label>
                        <input type="password" name="password" required autocomplete="current-password"
                               placeholder="••••••••"
                               class="mt-1.5 w-full rounded-xl border-slate-200 bg-slate-50 text-xs sm:text-sm focus:border-forest-600 focus:bg-white focus:ring-forest-600">
                    </div>

                    <div class="flex items-center justify-between text-xs sm:text-sm">
                        <label class="flex items-center gap-2 text-slate-600 cursor-pointer">
                            <input type="checkbox" name="remember" class="rounded border-slate-300 text-forest-700 focus:ring-forest-600">
                            <span>Ingat saya</span>
                        </label>
                    </div>

                    <button type="submit"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-forest-800 py-3 text-sm font-bold text-white shadow transition hover:bg-forest-900 active:scale-[0.98]">
                        <x-icon name="log-in" class="h-4 w-4" />
                        <span>Masuk ke Dashboard</span>
                    </button>

                    <div class="pt-2 text-center">
                        <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-slate-500 hover:text-forest-800 transition">
                            <x-icon name="arrow-left" class="h-3.5 w-3.5" />
                            <span>Kembali ke Beranda Website</span>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
