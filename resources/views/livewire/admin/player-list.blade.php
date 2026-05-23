<div class="max-w-4xl">
    <div class="mb-8">
        <h1 class="text-2xl font-black text-white">Players</h1>
        <p class="text-sm text-zinc-500 mt-1">Manage player accounts and balances</p>
    </div>

    {{-- Search --}}
    <div class="mb-6">
        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            placeholder="Search by name or email..."
            class="w-full max-w-md px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-white placeholder-zinc-500 focus:ring-2 focus:ring-yellow-500/50 focus:border-yellow-500/50 outline-none text-sm"
        >
    </div>

    {{-- Players Table --}}
    <div class="bg-white/5 rounded-2xl border border-white/10 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-white/10 bg-black/20">
                    <th class="text-left px-6 py-3 text-zinc-400 font-semibold">#</th>
                    <th class="text-left px-6 py-3 text-zinc-400 font-semibold">Name</th>
                    <th class="text-left px-6 py-3 text-zinc-400 font-semibold">Email</th>
                    <th class="text-right px-6 py-3 text-zinc-400 font-semibold">Balance</th>
                    <th class="text-left px-6 py-3 text-zinc-400 font-semibold">Joined</th>
                    <th class="text-center px-6 py-3 text-zinc-400 font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($players as $player)
                    <tr class="border-b border-white/5 hover:bg-white/[0.02] transition-colors">
                        <td class="px-6 py-3 text-zinc-500">{{ $player->id }}</td>
                        <td class="px-6 py-3 text-white font-medium">{{ $player->name }}</td>
                        <td class="px-6 py-3 text-zinc-400">{{ $player->email }}</td>
                        <td class="px-6 py-3 text-right">
                            @if ($editingPlayerId === $player->id)
                                <div class="flex items-center justify-end gap-2">
                                    <span class="text-xs text-zinc-500">Rp</span>
                                    <input
                                        type="number"
                                        wire:model="editBalance"
                                        min="0"
                                        class="w-32 px-2 py-1 bg-white/10 border border-yellow-500/50 rounded text-white text-right text-sm focus:ring-2 focus:ring-yellow-500/50 outline-none"
                                        autofocus
                                    >
                                </div>
                            @else
                                <span class="font-semibold {{ $player->balance > 0 ? 'text-emerald-400' : 'text-red-400' }}">
                                    Rp{{ number_format($player->balance, 0, ',', '.') }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-zinc-500 text-xs">
                            {{ $player->created_at->format('d M Y') }}
                        </td>
                        <td class="px-6 py-3 text-center">
                            @if ($editingPlayerId === $player->id)
                                <div class="flex items-center justify-center gap-2">
                                    <button
                                        wire:click="updateBalance"
                                        class="px-3 py-1 bg-green-500/20 text-green-400 rounded text-xs font-semibold hover:bg-green-500/30 transition-all border border-green-500/30"
                                    >
                                        Save
                                    </button>
                                    <button
                                        wire:click="cancelEdit"
                                        class="px-3 py-1 bg-white/10 text-zinc-400 rounded text-xs font-semibold hover:bg-white/20 transition-all"
                                    >
                                        Cancel
                                    </button>
                                </div>
                            @else
                                <button
                                    wire:click="editPlayer({{ $player->id }})"
                                    class="px-3 py-1 bg-yellow-500/20 text-yellow-400 rounded text-xs font-semibold hover:bg-yellow-500/30 transition-all border border-yellow-500/30"
                                >
                                    Edit Balance
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-zinc-500">
                            No players found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $players->links() }}
    </div>
</div>
