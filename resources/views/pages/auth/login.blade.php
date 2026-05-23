<x-layouts::mahesa-auth :title="__('Login')">
    <div class="flex flex-col gap-6">
        {{-- Header --}}
        <div class="text-center">
            <h1 class="text-xl font-bold text-white">Login ke Akun Anda</h1>
            <p class="text-sm text-zinc-500 mt-1">Masukkan email dan password untuk bermain</p>
        </div>

        {{-- Session Status --}}
        @if (session('status'))
            <div class="p-3 rounded-lg bg-green-500/20 text-green-400 text-sm text-center border border-green-500/30">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-4">
            @csrf

            {{-- Email --}}
            <div>
                <label for="email" class="block text-sm font-medium text-zinc-400 mb-1.5">Email</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="email"
                    placeholder="email@example.com"
                    class="w-full px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-white placeholder-zinc-600
                           focus:ring-2 focus:ring-yellow-500/50 focus:border-yellow-500/50 outline-none transition-all text-sm"
                >
                @error('email')
                    <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password --}}
            <div>
                <label for="password" class="block text-sm font-medium text-zinc-400 mb-1.5">Password</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                    class="w-full px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-white placeholder-zinc-600
                           focus:ring-2 focus:ring-yellow-500/50 focus:border-yellow-500/50 outline-none transition-all text-sm"
                >
                @error('password')
                    <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Remember Me --}}
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}
                       class="w-4 h-4 rounded bg-white/10 border-white/20 text-yellow-500 focus:ring-yellow-500/50">
                <span class="text-sm text-zinc-400">Ingat saya</span>
            </label>

            {{-- Submit --}}
            <button type="submit"
                    class="w-full py-3 rounded-xl font-bold text-sm uppercase tracking-wider transition-all
                           bg-gradient-to-r from-yellow-500 via-amber-500 to-yellow-500 text-black
                           hover:from-yellow-400 hover:via-amber-400 hover:to-yellow-400
                           active:scale-[0.98] shadow-lg shadow-yellow-500/20">
                🎰 Login
            </button>
        </form>

        <div class="text-center text-sm text-zinc-500">
            Belum punya akun?
            <a href="{{ route('register') }}" class="text-yellow-400 hover:text-yellow-300 font-semibold transition-colors">
                Daftar
            </a>
        </div>
    </div>
</x-layouts::mahesa-auth>
