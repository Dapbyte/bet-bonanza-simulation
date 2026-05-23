<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $title ?? 'Mahesa99 - Admin Panel' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="min-h-screen bg-[#0f0f1a] text-white font-sans">
    <div class="flex min-h-screen flex-col md:flex-row">
        <aside
            class="flex shrink-0 flex-col border-b border-white/10 bg-black/40 md:sticky md:top-0 md:h-screen md:w-64 md:border-b-0 md:border-r">
            <div class="border-b border-white/10 p-4 md:p-6">
                <span
                    class="bg-gradient-to-r from-yellow-400 via-amber-400 to-orange-500 bg-clip-text text-xl font-black text-transparent">
                    MAHESA99
                </span>
                <div class="mt-1 text-xs text-zinc-500">Admin Panel</div>
            </div>

            <nav class="flex gap-2 overflow-x-auto p-3 md:block md:flex-1 md:space-y-2 md:p-4">
                <a href="{{ route('admin.dashboard') }}" wire:navigate
                    class="flex shrink-0 items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-medium transition-all
                          {{ request()->routeIs('admin.dashboard') ? 'border border-yellow-500/30 bg-yellow-500/20 text-yellow-400' : 'text-zinc-400 hover:bg-white/5 hover:text-white' }}">
                    <span>⚙️</span>
                    <span>Game Settings</span>
                </a>
                <a href="{{ route('admin.players') }}" wire:navigate
                    class="flex shrink-0 items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-medium transition-all
                          {{ request()->routeIs('admin.players') ? 'border border-yellow-500/30 bg-yellow-500/20 text-yellow-400' : 'text-zinc-400 hover:bg-white/5 hover:text-white' }}">
                    <span>👥</span>
                    <span>Players</span>
                </a>
            </nav>

            <div class="mt-auto border-t border-white/10 bg-black/60 p-3 backdrop-blur md:p-4 md:sticky md:bottom-0">
                <div class="mb-3 flex items-center gap-3 px-2">
                    <div
                        class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-r from-yellow-400 to-orange-500 text-sm font-bold text-black">
                        A
                    </div>
                    <div class="min-w-0">
                        <div class="truncate text-sm font-medium">{{ Auth::user()->name }}</div>
                        <div class="text-xs text-zinc-500">Admin</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="flex w-full items-center gap-3 rounded-lg px-4 py-2 text-left text-sm text-red-400 transition-all hover:bg-red-500/10">
                        <span>🚪</span>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <div class="min-w-0 flex-1 overflow-auto">
            <main class="p-4 md:p-8">
                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts
</body>

</html>
