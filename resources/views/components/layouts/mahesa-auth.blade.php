<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $title ?? 'Mahesa99' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-gradient-to-b from-[#0a0a1a] via-[#120a25] to-[#1a0a2e] text-white font-sans antialiased">
    <div class="flex min-h-screen flex-col items-center justify-center p-6">
        <a href="/" class="mb-8 flex flex-col items-center gap-2">
            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-yellow-400 via-amber-500 to-orange-500 shadow-xl shadow-yellow-500/20">
                <span class="text-3xl">🎰</span>
            </div>
            <span class="bg-gradient-to-r from-yellow-400 via-amber-400 to-orange-500 bg-clip-text text-2xl font-black tracking-tight text-transparent">
                MAHESA99
            </span>
        </a>

        <div class="w-full max-w-sm">
            {{ $slot }}
        </div>

        <div class="mt-8 text-center text-xs text-zinc-600">
            <p>🎓 Simulasi Edukasi — Tidak menggunakan uang asli</p>
        </div>
    </div>

    @livewireScripts
</body>
</html>
