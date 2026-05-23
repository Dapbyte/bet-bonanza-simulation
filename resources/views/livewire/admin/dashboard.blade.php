<div class="max-w-5xl">
    <div class="mb-8">
        <h1 class="text-2xl font-black text-white">Game Settings</h1>
        <p class="text-sm text-zinc-500 mt-1">Configure the Sweet Bonanza simulation engine</p>
    </div>

    @if ($saveMessage)
        <div
            class="mb-6 rounded-xl border p-4 text-sm font-semibold {{ str_contains($saveMessage, 'Error') ? 'border-red-500/30 bg-red-500/20 text-red-400' : 'border-green-500/30 bg-green-500/20 text-green-400' }}">
            {{ $saveMessage }}
        </div>
    @endif

    @php
        $symbolNames = collect(\App\Services\SlotEngine::SYMBOLS)
            ->except(['scatter'])
            ->map(fn($symbol) => [$symbol['emoji'], $symbol['name']])
            ->all();
    @endphp

    <div class="space-y-6">
        <section class="rounded-2xl border border-white/10 bg-white/5 p-4 sm:p-6">
            <h2 class="mb-1 text-lg font-bold text-white">Symbol Weights</h2>
            <p class="mb-4 text-xs text-zinc-500">Bobot kemunculan bebas. Nilai lebih besar membuat simbol lebih sering
                muncul, tidak perlu total 100%.</p>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($symbolNames as $key => [$emoji, $name])
                    <div class="flex items-center gap-3 rounded-lg bg-black/20 p-3">
                        <span class="text-2xl">{{ $emoji }}</span>
                        <div class="min-w-0 flex-1">
                            <label class="block text-xs text-zinc-400">{{ $name }}</label>
                            <div class="mt-1 flex items-center gap-1">
                                <input type="number" wire:model.live="symbolChances.{{ $key }}" min="0"
                                    step="1" inputmode="numeric"
                                    class="w-20 rounded border border-white/20 bg-white/10 px-2 py-1 text-center text-sm text-white outline-none focus:border-yellow-500/50 focus:ring-2 focus:ring-yellow-500/50">
                                <span class="text-xs text-zinc-500">weight</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 flex flex-wrap items-center gap-3">
                <span class="text-sm text-zinc-400">Total weight:</span>
                <span class="text-lg font-black text-yellow-400">{{ $this->chancesTotal }}</span>
                <span class="text-xs text-zinc-500">Valid selama semua nilai tidak negatif.</span>
            </div>
        </section>

        <section class="rounded-2xl border border-white/10 bg-white/5 p-4 sm:p-6">
            <h2 class="mb-1 text-lg font-bold text-white">Payout Table</h2>
            <p class="mb-4 text-xs text-zinc-500">Kredit menang per simbol pada tiap tier jumlah.</p>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-sm">
                    <thead>
                        <tr class="border-b border-white/10">
                            <th class="py-2 text-left font-semibold text-zinc-400">Symbol</th>
                            <th class="py-2 text-center font-semibold text-zinc-400">8-9</th>
                            <th class="py-2 text-center font-semibold text-zinc-400">10-11</th>
                            <th class="py-2 text-center font-semibold text-zinc-400">12+</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($symbolNames as $key => [$emoji, $name])
                            <tr class="border-b border-white/5">
                                <td class="py-2">
                                    <span class="mr-2">{{ $emoji }}</span>
                                    <span class="text-zinc-300">{{ $name }}</span>
                                </td>
                                @foreach (['8', '10', '12'] as $tier)
                                    <td class="py-2 text-center">
                                        <input type="number"
                                            wire:model="multipliers.{{ $key }}.{{ $tier }}"
                                            min="0" step="1" inputmode="numeric"
                                            class="w-24 rounded border border-white/20 bg-white/10 px-2 py-1 text-center text-sm text-white outline-none focus:border-yellow-500/50 focus:ring-2 focus:ring-yellow-500/50">
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-2xl border border-white/10 bg-white/5 p-4 sm:p-6">
            <h2 class="mb-4 text-lg font-bold text-white">Other Settings</h2>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="rounded-lg bg-black/20 p-4">
                    <label class="mb-2 block text-sm text-zinc-400">Scatter Frequency</label>
                    <p class="mb-2 text-xs text-zinc-600">Paksa scatter setiap N spin. 0 berarti random.</p>
                    <input type="number" wire:model="scatterFrequency" min="0" step="1"
                        inputmode="numeric"
                        class="w-full rounded-lg border border-white/20 bg-white/10 px-3 py-2 text-white outline-none focus:border-yellow-500/50 focus:ring-2 focus:ring-yellow-500/50">
                </div>

                <div class="rounded-lg bg-black/20 p-4">
                    <label class="mb-2 block text-sm text-zinc-400">Default Balance</label>
                    <p class="mb-2 text-xs text-zinc-600">Saldo awal kredit virtual untuk player baru.</p>
                    <input type="number" wire:model="defaultBalance" min="0" step="1" inputmode="numeric"
                        class="w-full rounded-lg border border-white/20 bg-white/10 px-3 py-2 text-white outline-none focus:border-yellow-500/50 focus:ring-2 focus:ring-yellow-500/50">
                </div>

                <div class="rounded-lg bg-black/20 p-4">
                    <label class="mb-2 block text-sm text-zinc-400">Tumble / Cascade</label>
                    <p class="mb-2 text-xs text-zinc-600">Aktifkan simbol pecah lalu turun.</p>
                    <label class="relative inline-flex cursor-pointer items-center">
                        <input type="checkbox" wire:model="tumbleEnabled" class="peer sr-only">
                        <div
                            class="h-6 w-11 rounded-full bg-white/10 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-all after:content-[''] peer-checked:bg-yellow-500 peer-checked:after:translate-x-full peer-focus:ring-2 peer-focus:ring-yellow-500/50">
                        </div>
                        <span class="ml-3 text-sm text-zinc-300"
                            x-text="$wire.tumbleEnabled ? 'Enabled' : 'Disabled'"></span>
                    </label>
                </div>

                <div class="rounded-lg bg-black/20 p-4">
                    <label class="mb-2 block text-sm text-zinc-400">Max Multiplier</label>
                    <p class="mb-2 text-xs text-zinc-600">Batas maksimal multiplier dari tumble/runtuhan.</p>
                    <input type="number" wire:model="maxMultiplier" min="1" step="1" inputmode="numeric"
                        class="w-full rounded-lg border border-white/20 bg-white/10 px-3 py-2 text-white outline-none focus:border-yellow-500/50 focus:ring-2 focus:ring-yellow-500/50">
                </div>
            </div>
        </section>

        <div class="flex justify-end">
            <button wire:click="save" wire:loading.attr="disabled"
                class="rounded-xl bg-gradient-to-r from-yellow-500 via-amber-500 to-yellow-500 px-8 py-3 text-sm font-bold uppercase tracking-wider text-black shadow-lg shadow-yellow-500/25 transition-all hover:from-yellow-400 hover:via-amber-400 hover:to-yellow-400 active:scale-95 disabled:opacity-50">
                <span wire:loading.remove wire:target="save">Save Settings</span>
                <span wire:loading wire:target="save">Saving...</span>
            </button>
        </div>
    </div>
</div>
