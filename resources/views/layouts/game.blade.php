<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
    <title>{{ $title ?? 'Mahesa99 - Sweet Bonanza' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body
    class="min-h-screen overflow-x-hidden bg-gradient-to-b from-[#0a0a1a] via-[#120a25] to-[#1a0a2e] font-sans text-white">
    <nav class="sticky top-0 z-50 border-b border-yellow-500/20 bg-black/50 backdrop-blur-xl">
        <div class="mx-auto flex max-w-5xl items-center justify-between gap-3 px-3 py-3 sm:px-4">
            <div class="flex min-w-0 items-center gap-2">
                <span
                    class="truncate bg-gradient-to-r from-yellow-400 via-amber-400 to-orange-500 bg-clip-text text-xl font-black tracking-tight text-transparent sm:text-2xl">
                    MAHESA99
                </span>
                <span
                    class="hidden rounded-full bg-yellow-500/20 px-2.5 py-1 text-xs font-semibold uppercase tracking-wider text-yellow-300 min-[420px]:inline">
                    Sweet Bonanza
                </span>
            </div>
            <div class="flex shrink-0 items-center gap-2 sm:gap-3">
                <div
                    class="hidden items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1.5 sm:flex">
                    <span
                        class="grid h-6 w-6 place-items-center rounded-full bg-yellow-500/20 text-xs text-yellow-300">👤</span>
                    <span class="max-w-40 truncate text-sm text-zinc-200">{{ Auth::user()->name }}</span>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="rounded-full border border-red-500/30 bg-red-500/20 px-3.5 py-1.5 text-xs font-semibold text-red-300 transition-all hover:bg-red-500/30">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <main class="flex-1">
        {{ $slot }}
    </main>

    @livewireScripts
</body>

</html>
