<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('homestay.name').' - Penginapan Nyaman di Anyer')</title>
    <meta name="description" content="@yield('description', 'Informasi kamar dan ketersediaan '.config('homestay.name').'. Pemesanan melalui WhatsApp Business pengelola.')">
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}">
    <script src="https://cdn.tailwindcss.com/3.4.17?plugins=forms"></script>
    <script>
        tailwind.config = {
            theme: { extend: {
                colors: {
                    brand: {
                        50:  '#f6f7f4',
                        100: '#e8eae2',
                        200: '#d2d6c6',
                        300: '#b4baa2',
                        400: '#95a07f',
                        500: '#788663',
                        600: '#5e6b4e',
                        700: '#4a5540',
                        800: '#3d4636',
                        900: '#2f3529',
                        950: '#1a1e17',
                    },
                    forest: {
                        50:  '#f2f7f2',
                        100: '#e0ece0',
                        200: '#c3d9c3',
                        300: '#97bc97',
                        400: '#6a9a6a',
                        500: '#477e47',
                        600: '#356535',
                        700: '#2c512c',
                        800: '#264126',
                        900: '#1f3520',
                        950: '#0e1d0f',
                    },
                    sand: {
                        50:  '#faf8f3',
                        100: '#f3efe4',
                        200: '#e6ddc8',
                        300: '#d6c7a5',
                        400: '#c4ac81',
                        500: '#b89768',
                        600: '#ab835b',
                        700: '#8e6b4c',
                        800: '#745842',
                        900: '#5f4938',
                        950: '#33251c',
                    },
                    warm: {
                        50:  '#fdfcfa',
                        100: '#f9f6f0',
                        200: '#f2ecdf',
                        300: '#e8dfc9',
                        400: '#dccfaf',
                    },
                },
                fontFamily: {
                    sans:  ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    serif: ['"Playfair Display"', 'Georgia', 'serif'],
                },
            } }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <style>
        [x-cloak]{display:none!important}
        .hero-overlay {
            background: linear-gradient(180deg, rgba(14,29,15,0.78) 0%, rgba(20,38,21,0.65) 45%, rgba(14,29,15,0.92) 100%);
        }
    </style>
    @stack('head')
</head>
<body class="bg-warm-50 font-sans text-slate-700 antialiased selection:bg-forest-100 selection:text-forest-900">
    @yield('content')

    @include('partials.chatbot')
    @stack('scripts')
</body>
</html>
