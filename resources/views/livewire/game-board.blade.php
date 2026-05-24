<div x-data="slotGame(@js(\App\Services\SlotEngine::SYMBOLS))" x-on:spin-started.window="startLocalSpin()"
    x-on:spin-result.window="handleSpinResult($event.detail)"
    class="mx-auto w-full max-w-5xl select-none px-2 py-3 sm:px-4 sm:py-6" wire:ignore>
    <!-- Free Spins Intro Modal -->
    <div x-show="showFSIntro" x-transition
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/85 px-4 backdrop-blur-md"
        style="display: none;">
        <div
            class="w-full max-w-md rounded-3xl border border-pink-500/40 bg-gradient-to-b from-[#2a0a4f] to-[#150428] p-6 text-center shadow-2xl shadow-pink-500/30">
            <div class="text-6xl mb-3">🍭</div>
            <h2
                class="bg-gradient-to-r from-yellow-400 via-pink-500 to-yellow-400 bg-clip-text text-3xl font-black text-transparent sm:text-4xl">
                BONUS GAME
            </h2>
            <p class="mt-2 text-sm text-zinc-300">Babak Putaran Gratis dimulai...</p>
            <div class="my-6 rounded-2xl border border-white/10 bg-white/5 py-4">
                <span class="text-6xl font-black text-yellow-400">10</span>
                <span class="block mt-1 text-xs uppercase tracking-widest text-zinc-400">Putaran Gratis</span>
            </div>
            <div class="text-xs uppercase tracking-[0.3em] text-zinc-500">Auto</div>
        </div>
    </div>

    <!-- Free Spins Outro Modal -->
    <div x-show="showFSOutro" x-transition
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/85 px-4 backdrop-blur-md"
        style="display: none;">
        <div
            class="w-full max-w-md rounded-3xl border-2 border-yellow-500 bg-gradient-to-b from-[#1b082e] to-[#0a0214] p-6 text-center shadow-2xl shadow-yellow-500/30">
            <div class="text-6xl mb-3">🏆</div>
            <h2
                class="bg-gradient-to-r from-yellow-400 via-amber-300 to-yellow-500 bg-clip-text text-3xl font-black text-transparent sm:text-4xl">
                TOTAL KEMENANGAN
            </h2>
            <p class="mt-2 text-sm text-zinc-300">Kemenangan luar biasa dari Putaran Gratis!</p>
            <div class="my-6 rounded-2xl bg-yellow-500/10 py-5 border border-yellow-500/20">
                <span class="text-3xl font-black text-green-400 sm:text-4xl"
                    x-text="'Rp' + formatCredit(fsWinTotal)"></span>
                <span class="block mt-1 text-xs uppercase tracking-widest text-zinc-400">Total Kredit Virtual</span>
            </div>
            <button @click="closeFreeSpinsOutro()"
                class="w-full rounded-2xl bg-gradient-to-r from-yellow-500 to-amber-600 py-3.5 text-sm font-black uppercase text-black shadow-lg active:scale-95">
                KEMBALI KE GAME
            </button>
        </div>
    </div>

    <!-- Free Spins Re-trigger Banner -->
    <div x-show="showFSReTrigger" x-transition
        class="pointer-events-none fixed inset-0 z-50 flex items-center justify-center px-4" style="display: none;">
        <div
            class="rounded-2xl border-2 border-yellow-400 bg-[#31065c] px-8 py-5 text-center shadow-2xl shadow-yellow-500/50">
            <div class="text-4xl mb-2">🍭</div>
            <h3 class="text-2xl font-black text-yellow-400 sm:text-3xl">+5 PUTARAN GRATIS!</h3>
            <p class="text-xs text-zinc-300 uppercase tracking-widest">Babak Bonus Ditambahkan</p>
        </div>
    </div>

    <!-- Multiplier Flash Overlay -->
    <div x-show="showMultiplierFlash" x-transition
        class="pointer-events-none fixed inset-x-0 top-1/3 z-50 flex justify-center px-4" style="display: none;">
        <div
            class="multiplier-glow rounded-xl bg-gradient-to-r from-yellow-500 via-amber-400 to-yellow-500 px-5 py-3 shadow-2xl shadow-yellow-500/50 sm:px-8 sm:py-4">
            <span class="text-2xl font-black tracking-tight text-black sm:text-4xl">
                TOTAL PENGALI x<span x-text="flashMultiplierValue"></span>
            </span>
        </div>
    </div>

    <!-- Top Bonus Display Banner (only shows when in Free Spins) -->
    <div x-show="inFSMode" x-transition
        class="mb-4 rounded-2xl border-2 border-pink-500 bg-gradient-to-r from-[#21043d] via-[#4d075e] to-[#21043d] p-3 text-center shadow-lg"
        style="display: none;">
        <div class="flex items-center justify-around gap-4">
            <div>
                <span class="block text-[10px] font-bold uppercase tracking-wider text-pink-400">Putaran Gratis
                    Tersisa</span>
                <span class="text-2xl font-black text-yellow-400" x-text="fsRemaining"></span>
            </div>
            <div class="h-8 w-px bg-pink-500/20"></div>
            <div>
                <span class="block text-[10px] font-bold uppercase tracking-wider text-pink-400">Kemenangan Babak
                    Bonus</span>
                <span class="text-2xl font-black text-green-400" x-text="'Rp' + formatCredit(fsWinTotal)"></span>
            </div>
        </div>
    </div>

    <!-- Main Game Section (Flex/Grid for Side-by-Side Layout) -->
    <div class="flex flex-col md:flex-row gap-4 items-stretch justify-center">

        <!-- Left Panel: Tumble History (visible/updates beside the grid) -->
        <div class="w-full md:w-72 shrink-0 flex flex-col">
            <div class="relative rounded-2xl p-1 h-full flex flex-col transition-all duration-300"
                :class="inFSMode ? 'bg-gradient-to-b from-pink-500/40 via-purple-500/20 to-pink-500/40 shadow-pink-500/15' :
                    'bg-gradient-to-b from-purple-500/30 via-pink-500/10 to-purple-500/30'">
                <div
                    class="flex-1 rounded-xl border border-purple-500/20 bg-[#160d2b]/95 p-4 shadow-xl shadow-black/30 backdrop-blur-sm flex flex-col justify-between min-h-[160px] md:min-h-[400px]">
                    <div>
                        <div class="mb-3 flex items-center justify-between border-b border-white/10 pb-2">
                            <h3 class="text-xs font-black tracking-wider text-yellow-400 sm:text-sm">DAFTAR KEMENANGAN
                            </h3>
                            <div x-show="currentMultiplier > 1"
                                class="rounded-full bg-yellow-500/30 px-2.5 py-0.5 text-xs font-black text-yellow-300">
                                🔥 x<span x-text="currentMultiplier"></span>
                            </div>
                        </div>

                        <!-- Tumble Win Entries -->
                        <div class="space-y-1.5 max-h-[180px] md:max-h-[300px] overflow-y-auto pr-1">
                            <template x-for="(item, i) in tumblePanelData" :key="i">
                                <div
                                    class="flex items-center justify-between gap-3 border-b border-white/5 py-1.5 last:border-0">
                                    <div class="flex min-w-0 items-center gap-2">
                                        <span class="text-lg" x-text="item.emoji"></span>
                                        <span class="truncate text-xs text-zinc-300 sm:text-sm"
                                            x-text="item.name"></span>
                                        <span class="text-xs text-zinc-500">x<span x-text="item.count"></span></span>
                                    </div>
                                    <span class="shrink-0 text-xs font-semibold text-green-400 sm:text-sm">
                                        +Rp<span x-text="formatCredit(item.payout)"></span>
                                    </span>
                                </div>
                            </template>

                            <template x-if="multiplierBombs.length > 0">
                                <div class="mt-2 rounded-lg border border-yellow-500/20 bg-yellow-500/10 px-2 py-1.5">
                                    <div class="mb-1 text-[10px] font-black uppercase tracking-wider text-yellow-300">
                                        Pengali Bom
                                    </div>
                                    <div class="flex flex-wrap gap-1">
                                        <template x-for="bomb in multiplierBombs" :key="bomb.position">
                                            <span
                                                class="rounded bg-black/30 px-1.5 py-0.5 text-[10px] font-black text-yellow-200">
                                                x<span x-text="bomb.multiplier"></span>
                                            </span>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            <div x-show="tumblePanelData.length === 0"
                                class="py-12 text-center text-xs text-zinc-500 italic">
                                Putar slot untuk melihat kemenangan...
                            </div>
                        </div>
                    </div>

                    <!-- History Total -->
                    <div class="mt-4 border-t border-white/20 pt-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-zinc-400 uppercase tracking-wider">Subtotal</span>
                            <span class="text-right text-sm font-black text-green-400 sm:text-lg">
                                <template x-if="currentMultiplier > 1">
                                    <span>Rp<span x-text="formatCredit(tumbleSubtotal)"></span> × x<span
                                            x-text="currentMultiplier"></span></span>
                                </template>
                                <template x-if="currentMultiplier <= 1">
                                    <span>+Rp<span x-text="formatCredit(tumbleSubtotal)"></span></span>
                                </template>
                            </span>
                        </div>
                        <div x-show="inFSMode && tumblePanelData.length > 0"
                            class="mt-2 flex items-center justify-between border-t border-white/10 pt-2"
                            style="display: none;">
                            <span class="text-xs font-bold text-zinc-400 uppercase tracking-wider">Total</span>
                            <span class="text-sm font-black text-yellow-300 sm:text-lg">+Rp<span
                                    x-text="formatCredit(multipliedTotal)"></span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel: Main Grid Frame -->
        <div class="flex-1 min-w-0 relative rounded-2xl p-1 transition-all duration-300"
            :class="inFSMode ?
                'bg-gradient-to-b from-pink-500/50 via-purple-500/30 to-pink-500/50 shadow-2xl shadow-pink-500/20' :
                'bg-gradient-to-b from-yellow-500/30 via-amber-600/20 to-yellow-500/30'">
            <div class="rounded-xl border border-yellow-500/10 bg-[#0d0b1a] p-2 sm:p-4">
                <div class="relative grid grid-cols-6 gap-1.5 rounded-lg sm:gap-2" x-ref="boardGrid">
                    @for ($i = 0; $i < 30; $i++)
                        <div class="relative flex aspect-square min-w-0 items-center justify-center overflow-visible rounded-lg border border-white/10 bg-gradient-to-br from-white/5 to-white/[0.02]"
                            :class="{
                                'ring-2 ring-yellow-400/80 bg-yellow-500/10': winPositions.includes(
                                    {{ $i }}),
                            }">

                            <!-- Individual Win Payout Popups -->
                            <div x-show="symbolPopups[{{ $i }}]" x-transition
                                class="absolute -top-3 left-1/2 z-45 max-w-[8rem] -translate-x-1/2 rounded border border-yellow-500/30 bg-black/95 px-1.5 py-1 text-center text-[9px] font-bold leading-tight text-yellow-400 shadow-lg sm:max-w-none sm:whitespace-nowrap sm:text-xs"
                                x-text="symbolPopups[{{ $i }}]"></div>
                        </div>
                    @endfor

                    <div class="pointer-events-none absolute inset-0 z-10 overflow-visible">
                        <template x-for="piece in symbolPieces" :key="piece.id">
                            <div class="symbol-piece"
                                :class="{
                                    'z-20': piece.popping,
                                    'symbol-piece-popping': piece.popping,
                                    'symbol-piece-entering': piece.entering,
                                }"
                                :style="symbolPieceStyle(piece)">
                                <template x-if="!isBomb(piece.symbol)">
                                    <span
                                        class="grid h-full w-full place-items-center text-[clamp(1.25rem,8vw,2.5rem)]"
                                        :class="{ 'symbol-pop': piece.popping }"
                                        x-text="getSymbolEmoji(piece.symbol)"></span>
                                </template>

                                <template x-if="isBomb(piece.symbol)">
                                    <div class="relative flex h-full w-full items-center justify-center"
                                        :class="{ 'symbol-pop': piece.popping }">
                                        <span class="text-[clamp(1.25rem,8vw,2.5rem)]"
                                            x-text="getSymbolEmoji(piece.symbol)"></span>
                                        <span
                                            class="absolute bottom-1 right-1 rounded px-1 text-[9px] font-black uppercase text-black shadow-md"
                                            :class="getBombBadgeColor(piece.symbol)"
                                            x-text="getBombMultiplierValue(piece.symbol)"></span>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Controls Row (Lag-free bet updates) -->
    <div class="mt-4 grid grid-cols-1 gap-2 sm:mt-5 sm:grid-cols-3 sm:gap-3">
        <div class="rounded-xl border border-white/10 bg-white/5 p-3">
            <label class="mb-2 block text-[10px] font-semibold uppercase tracking-wider text-zinc-500">Coin
                Value</label>
            <div class="grid grid-cols-3 gap-1">
                @foreach ([100, 200, 500] as $val)
                    <button @click="setCoinValue({{ $val }})" :disabled="isAnimating || inFSMode"
                        class="rounded-lg py-2 text-xs font-bold transition-all disabled:opacity-50"
                        :class="coinValue === {{ $val }} ? 'bg-yellow-500 text-black' :
                            'bg-white/10 text-zinc-400 hover:bg-white/20'">
                        {{ $val }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="rounded-xl border border-white/10 bg-white/5 p-3">
            <label class="mb-2 block text-[10px] font-semibold uppercase tracking-wider text-zinc-500">Bet</label>
            <div class="flex items-center gap-2">
                <button @click="setBet(bet - 1)" :disabled="isAnimating || inFSMode"
                    class="grid h-9 w-9 place-items-center rounded-lg bg-white/10 text-lg font-bold text-white transition-all hover:bg-white/20 disabled:opacity-50">-</button>
                <div class="min-w-0 flex-1 text-center text-lg font-black text-white" x-text="bet"></div>
                <button @click="setBet(bet + 1)" :disabled="isAnimating || inFSMode"
                    class="grid h-9 w-9 place-items-center rounded-lg bg-white/10 text-lg font-bold text-white transition-all hover:bg-white/20 disabled:opacity-50">+</button>
            </div>
        </div>

        <div class="rounded-xl border border-white/10 bg-white/5 p-3">
            <label class="mb-2 block text-[10px] font-semibold uppercase tracking-wider text-zinc-500">Total
                Bet</label>
            <div class="truncate text-center text-lg font-black text-yellow-400"
                x-text="'Rp' + formatCredit(bet * coinValue)"></div>
        </div>
    </div>

    <!-- Credit, Bet, Wins Display Panel -->
    <div class="mt-2 grid grid-cols-3 gap-2 sm:mt-3 sm:gap-3">
        <div class="rounded-xl border border-emerald-500/20 bg-emerald-950/40 p-2 text-center sm:p-3">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-emerald-400/60">Credit</div>
            <div class="truncate text-sm font-black text-emerald-400 sm:text-lg"
                x-text="'Rp' + formatCredit(displayCredit)"></div>
        </div>
        <div class="rounded-xl border border-blue-500/20 bg-blue-950/40 p-2 text-center sm:p-3">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-blue-400/60">Bet</div>
            <div class="truncate text-sm font-black text-blue-400 sm:text-lg"
                x-text="inFSMode ? 'FREE' : 'Rp' + formatCredit(bet * coinValue)"></div>
        </div>
        <div class="rounded-xl border border-yellow-500/20 bg-yellow-950/40 p-2 text-center sm:p-3">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-yellow-400/60">Wins</div>
            <div class="truncate text-sm font-black text-yellow-400 sm:text-lg"
                x-text="'Rp' + formatCredit(displayWins)"></div>
        </div>
    </div>

    <!-- Spin Buttons Row -->
    <div class="mt-3 flex gap-2 sm:mt-4 sm:gap-3">
        <button @click="triggerSpin()" :disabled="isAnimating" x-show="!autoplay"
            class="min-w-0 flex-1 rounded-xl bg-gradient-to-r from-yellow-500 via-amber-500 to-yellow-500 py-4 text-base font-black uppercase tracking-wider text-black shadow-lg shadow-yellow-500/25 transition-all hover:brightness-110 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50 sm:text-lg">
            <span x-show="!isAnimating">SPIN</span>
            <span x-show="isAnimating" style="display: none;">SPINNING...</span>
        </button>

        <div x-show="!autoplay" class="relative" x-data="{ showAutoMenu: false }">
            <button @click="showAutoMenu = !showAutoMenu" :disabled="isAnimating"
                class="h-full rounded-xl border border-white/20 bg-white/10 px-4 text-xs font-bold uppercase tracking-wider text-white transition-all hover:bg-white/20 disabled:cursor-not-allowed disabled:opacity-50 sm:px-6 sm:text-sm">
                AUTO
            </button>

            <div x-show="showAutoMenu" @click.outside="showAutoMenu = false" x-transition
                class="absolute bottom-full right-0 z-40 mb-2 min-w-[120px] rounded-xl border border-white/20 bg-[#1a1a2e] p-2 shadow-2xl"
                style="display: none;">
                @foreach ([10, 25, 50, 100] as $count)
                    <button @click="startAutoplay({{ $count }}); showAutoMenu = false"
                        class="w-full rounded-lg px-4 py-2 text-left text-sm text-zinc-300 transition-all hover:bg-yellow-500/20 hover:text-yellow-400">
                        {{ $count }} Spins
                    </button>
                @endforeach
            </div>
        </div>

        <button x-show="autoplay" @click="stopAutoplay()"
            class="min-w-0 flex-1 rounded-xl bg-gradient-to-r from-red-600 to-red-500 py-4 text-lg font-black uppercase tracking-wider text-white shadow-lg shadow-red-500/25 transition-all hover:from-red-500 hover:to-red-400 active:scale-95"
            style="display: none;">
            STOP
        </button>

        <div x-show="autoplay" class="flex items-center rounded-xl border border-white/10 bg-white/5 px-3"
            style="display: none;">
            <span class="text-xs text-zinc-400">Left:</span>
            <span class="ml-2 text-lg font-black text-yellow-400" x-text="autoplayRemaining"></span>
        </div>
    </div>
</div>

@script
    <script>
        Alpine.data('slotGame', (symbolMap) => ({
            currentGrid: @js($grid),
            symbolPieces: [],
            nextPieceId: 1,
            winPositions: [],
            poppingPositions: [],
            symbolPopups: {},
            isAnimating: false,
            showMultiplierFlash: false,
            flashMultiplierValue: 1,
            tumblePanelData: [],
            tumbleSubtotal: 0,
            currentMultiplier: 1,
            multipliedTotal: 0,
            spinSessionWin: 0,
            multiplierBombs: [],

            // Interactive bet properties (client-side managed to remove lag)
            bet: @js($bet),
            coinValue: @js($coinValue),
            displayCredit: @js($credit),
            displayWins: 0,
            totalWins: 0,
            spinStartWins: 0,

            // Free Spins properties
            inFSMode: @js($inFreeSpins),
            fsRemaining: @js($freeSpinsRemaining),
            fsWinTotal: @js($totalFreeSpinWin),
            showFSIntro: false,
            showFSOutro: false,
            showFSReTrigger: false,
            fsAutoStartTimer: null,
            pendingAutoplayActive: false,
            pendingAutoplayRemaining: 0,

            // Autoplay properties
            autoplay: false,
            autoplayRemaining: 0,

            spinInterval: null,

            init() {
                for (let i = 0; i < 30; i++) {
                    this.symbolPopups[i] = null;
                }
                this.syncPiecesToGrid(this.currentGrid);
                this.displayWins = this.totalWins;
            },

            syncPiecesToGrid(grid) {
                this.symbolPieces = grid
                    .map((symbol, pos) => symbol === null ? null : this.createPiece(symbol, pos))
                    .filter(Boolean);
            },

            createPiece(symbol, pos, row = Math.floor(pos / 6), col = pos % 6) {
                return {
                    id: this.nextPieceId++,
                    symbol,
                    row,
                    visualRow: row,
                    col,
                    duration: 0,
                    popping: false,
                    entering: false,
                };
            },

            symbolPieceStyle(piece) {
                return [
                    `--piece-row: ${piece.visualRow ?? piece.row}`,
                    `--piece-col: ${piece.col}`,
                    `--piece-duration: ${piece.duration || 0}ms`,
                ].join('; ');
            },

            positionFromPiece(piece) {
                return Math.round(piece.row) * 6 + piece.col;
            },

            getSymbolEmoji(symbolId) {
                return symbolMap[symbolId]?.emoji || '';
            },

            isBomb(symbolId) {
                return symbolId !== null && symbolId.startsWith('bomb_');
            },

            getBombMultiplierValue(symbolId) {
                if (!symbolId) return '';
                return symbolId.replace('bomb_', '') + 'x';
            },

            getBombBadgeColor(symbolId) {
                if (!symbolId) return 'bg-yellow-400';
                const val = parseInt(symbolId.replace('bomb_', ''));
                if (val <= 2) return 'bg-cyan-400 text-black';
                if (val <= 5) return 'bg-green-400 text-black';
                if (val <= 10) return 'bg-blue-400 text-white';
                if (val <= 25) return 'bg-pink-400 text-black';
                if (val <= 50) return 'bg-purple-500 text-white';
                return 'bg-yellow-400 text-black border border-yellow-200 animate-pulse';
            },

            formatCredit(value) {
                return new Intl.NumberFormat('id-ID').format(value || 0);
            },

            allColumns() {
                return new Set([0, 1, 2, 3, 4, 5]);
            },

            setBet(value) {
                this.bet = Math.max(1, Math.min(10, value));
            },

            setCoinValue(value) {
                this.coinValue = value;
            },

            triggerSpin() {
                if (this.isAnimating) return;

                const totalBet = this.bet * this.coinValue;
                if (!this.inFSMode && this.displayCredit < totalBet) {
                    this.dispatchToast('Saldo tidak cukup!', 'error');
                    this.stopAutoplay();
                    return;
                }

                // Call the Livewire spin method, passing client values
                this.$wire.spin(this.bet, this.coinValue);
            },

            startAutoplay(count) {
                if (this.isAnimating) return;
                this.autoplay = true;
                this.autoplayRemaining = count;
                this.triggerSpin();
            },

            stopAutoplay() {
                this.autoplay = false;
                this.autoplayRemaining = 0;
            },

            startLocalSpin() {
                this.isAnimating = true;
                this.winPositions = [];
                this.poppingPositions = [];
                this.tumblePanelData = [];
                this.tumbleSubtotal = 0;
                this.currentMultiplier = 1;
                this.multipliedTotal = 0;
                this.spinSessionWin = 0;
                this.multiplierBombs = [];
                this.spinStartWins = this.totalWins;
                this.showMultiplierFlash = false;

                // Clear existing interval
                if (this.spinInterval) {
                    clearInterval(this.spinInterval);
                }

                this.spinInterval = null;
            },

            async handleSpinResult(detail) {
                const data = Array.isArray(detail) ? detail[0] : detail;
                const {
                    tumbles,
                    baseWin,
                    multiplierSum,
                    finalWin,
                    bombs,
                    finalGrid,
                    credit,
                    freeSpinsTriggered,
                    freeSpinsReTriggered,
                    freeSpinsRemaining,
                    totalFreeSpinWin,
                    freeSpinsFinished
                } = data;

                this.fsRemaining = freeSpinsRemaining;
                this.fsWinTotal = totalFreeSpinWin;

                // Clear spin shuffle interval
                if (this.spinInterval) {
                    clearInterval(this.spinInterval);
                    this.spinInterval = null;
                }

                if (tumbles.length === 0) {
                    this.currentGrid = finalGrid;
                    await this.dropInGrid(this.currentGrid);
                    this.spinSessionWin = 0;
                    this.tumbleSubtotal = 0;
                    this.multipliedTotal = 0;
                    if (finalWin > 0) {
                        this.totalWins = this.spinStartWins + finalWin;
                        await this.animateCounter('displayWins', this.totalWins, 320);
                    }
                    this.displayCredit = credit;
                    this.isAnimating = false;
                    await this.handleFSTransitions(freeSpinsTriggered, freeSpinsFinished, totalFreeSpinWin,
                        freeSpinsRemaining);
                    await this.continueAutoplayIfNeeded();
                    return;
                }

                const startingGrid = tumbles.length > 0 ? tumbles[0].grid : finalGrid;
                this.currentGrid = [...startingGrid];
                await this.dropInGrid(this.currentGrid);

                let accumulatedBaseWin = 0;
                this.multiplierBombs = bombs || [];

                for (let t = 0; t < tumbles.length; t++) {
                    const tumble = tumbles[t];
                    this.currentGrid = tumble.grid;
                    await this.sleep(150);

                    this.winPositions = [];
                    this.poppingPositions = [];

                    // Identify winning positions
                    for (const win of tumble.wins) {
                        for (const pos of win.positions) {
                            this.winPositions.push(pos);
                            this.symbolPopups[pos] =
                                `${win.emoji} x${win.count} -> +Rp${this.formatCredit(win.payout)}`;
                        }
                    }

                    const affectedColumns = new Set(this.winPositions.map((pos) => pos % 6));

                    // Keep bombs visible (do not pop them)
                    await this.sleep(550);

                    // Append tumble list for this spin
                    this.tumblePanelData = [...this.tumblePanelData, ...tumble.wins];
                    accumulatedBaseWin += tumble.subtotal;
                    this.spinSessionWin = accumulatedBaseWin;
                    this.tumbleSubtotal = accumulatedBaseWin;
                    this.multipliedTotal = this.spinSessionWin;

                    await this.animateCounter('displayWins', this.spinStartWins + this.spinSessionWin, 300);
                    await this.sleep(180);

                    // Pop/explode winning symbols
                    this.poppingPositions = [...new Set(this.winPositions)];
                    for (const piece of this.symbolPieces) {
                        piece.popping = this.poppingPositions.includes(this.positionFromPiece(piece));
                    }
                    await this.sleep(260);

                    // Empty the popped elements
                    const blankGrid = [...this.currentGrid];
                    for (const pos of this.winPositions) {
                        blankGrid[pos] = null;
                        this.symbolPopups[pos] = null;
                    }
                    this.currentGrid = blankGrid;
                    this.symbolPieces = this.symbolPieces.filter((piece) => !piece.popping);
                    await this.sleep(120);

                    // Drop remaining symbols and fill new ones (Gravitational cascade)
                    const nextGrid = tumble.nextGrid || finalGrid;

                    const nextGridApplied = [...this.currentGrid];
                    for (let pos = 0; pos < 30; pos++) {
                        if (affectedColumns.has(pos % 6)) {
                            nextGridApplied[pos] = nextGrid[pos];
                        }
                    }

                    await this.animateCascadeToGrid(blankGrid, nextGridApplied,
                        affectedColumns);

                    // Apply the updated grid after the fall completes
                    this.currentGrid = nextGridApplied;

                    this.winPositions = [];
                    this.poppingPositions = [];
                }

                // Apply Multiplier Bombs if present at the end of the tumble loop
                if (multiplierSum > 0 && accumulatedBaseWin > 0) {
                    this.currentMultiplier = multiplierSum;
                    this.spinSessionWin = finalWin;
                    this.multipliedTotal = this.spinSessionWin;
                    await this.popMultiplierBombs(this.multiplierBombs, finalGrid);

                    // Flash the total accumulated multiplier bomb sum
                    this.flashMultiplierValue = multiplierSum;
                    this.showMultiplierFlash = true;
                    await this.sleep(700);
                    this.showMultiplierFlash = false;

                    // Animate wins counter to final multiplied value
                    await this.animateCounter('displayWins', this.spinStartWins + this.spinSessionWin, 360);
                    await this.sleep(180);
                }

                // Check if there was a re-trigger in Free Spins
                if (freeSpinsReTriggered) {
                    this.fsRemaining = freeSpinsRemaining;
                    this.showFSReTrigger = true;
                    await this.sleep(1500);
                    this.showFSReTrigger = false;
                }

                // Update final grid and credit balance
                if (!(multiplierSum > 0 && accumulatedBaseWin > 0)) {
                    this.currentGrid = finalGrid;
                }
                this.displayCredit = credit;
                if (this.inFSMode) {
                    this.fsWinTotal = totalFreeSpinWin;
                    this.fsRemaining = freeSpinsRemaining;
                }

                this.totalWins = this.spinStartWins + finalWin;

                await this.sleep(300);
                this.isAnimating = false;

                // Process FS triggers and autoplays
                await this.handleFSTransitions(freeSpinsTriggered, freeSpinsFinished, totalFreeSpinWin,
                    freeSpinsRemaining);
                await this.continueAutoplayIfNeeded();
            },

            async animateCascadeToGrid(blankGrid, nextGrid, affectedColumns) {
                let maxDuration = 0;

                for (const piece of this.symbolPieces) {
                    piece.duration = 0;
                    piece.entering = false;
                }

                const enteringPieces = [];
                const movingPieces = [];

                for (let col = 0; col < 6; col++) {
                    if (affectedColumns && !affectedColumns.has(col)) {
                        continue;
                    }

                    const columnPieces = this.symbolPieces
                        .filter((piece) => piece.col === col)
                        .sort((a, b) => (b.visualRow ?? b.row) - (a.visualRow ?? a.row));

                    let emptyCount = 0;
                    for (let row = 0; row < 5; row++) {
                        const pos = row * 6 + col;
                        if (blankGrid[pos] === null && nextGrid[pos] !== null) {
                            emptyCount++;
                        }
                    }

                    for (let i = 0; i < columnPieces.length; i++) {
                        const piece = columnPieces[i];
                        const targetRow = 4 - i;
                        const currentRow = piece.visualRow ?? piece.row;
                        const distance = Math.max(0, targetRow - currentRow);
                        const duration = distance > 0 ? 360 + distance * 90 : 0;

                        piece.row = targetRow;
                        movingPieces.push({
                            piece,
                            targetRow,
                            duration,
                        });
                        maxDuration = Math.max(maxDuration, duration);
                    }

                    for (let row = 0; row < emptyCount; row++) {
                        const pos = row * 6 + col;
                        const symbol = nextGrid[pos];

                        if (symbol === null) {
                            continue;
                        }

                        const spawnRow = -1.15 - (emptyCount - row - 1) * 1.05;
                        const distance = row - spawnRow;
                        const duration = 360 + Math.ceil(distance) * 90;
                        const piece = this.createPiece(symbol, pos, row, col);

                        piece.visualRow = spawnRow;
                        piece.duration = 0;
                        piece.entering = true;
                        this.symbolPieces.push(piece);
                        enteringPieces.push({
                            piece,
                            targetRow: row,
                            duration,
                        });

                        maxDuration = Math.max(maxDuration, duration);
                    }
                }

                await this.forcePaint();

                for (const move of movingPieces) {
                    move.piece.duration = move.duration;
                }

                for (const entry of enteringPieces) {
                    entry.piece.duration = entry.duration;
                }

                await this.forcePaint();

                for (const move of movingPieces) {
                    move.piece.visualRow = move.targetRow;
                }

                for (const entry of enteringPieces) {
                    entry.piece.visualRow = entry.targetRow;
                }

                if (maxDuration > 0) {
                    await this.sleep(maxDuration + 140);
                }

                for (const piece of this.symbolPieces) {
                    piece.duration = 0;
                    piece.entering = false;
                }
            },

            async dropInGrid(grid) {
                const blankGrid = Array(30).fill(null);
                this.currentGrid = blankGrid;
                this.symbolPieces = [];
                await this.nextFrame();
                await this.sleep(120);

                const fallingPieces = [];
                let maxDuration = 0;

                for (let pos = 0; pos < 30; pos++) {
                    const symbol = grid[pos];
                    if (symbol === null) continue;

                    const row = Math.floor(pos / 6);
                    const col = pos % 6;
                    const spawnRow = row - 5.5;
                    const distance = row - spawnRow;
                    const duration = 560 + Math.ceil(distance) * 120;

                    const piece = this.createPiece(symbol, pos, row, col);
                    piece.visualRow = spawnRow;
                    piece.duration = 0;
                    piece.entering = true;
                    this.symbolPieces.push(piece);
                    fallingPieces.push({
                        piece,
                        targetRow: row,
                        duration,
                    });

                    maxDuration = Math.max(maxDuration, duration);
                }

                await this.forcePaint();

                for (const entry of fallingPieces) {
                    entry.piece.duration = entry.duration;
                }

                await this.forcePaint();

                for (const entry of fallingPieces) {
                    entry.piece.visualRow = entry.targetRow;
                }

                if (maxDuration > 0) {
                    await this.sleep(maxDuration + 220);
                }

                for (const piece of this.symbolPieces) {
                    piece.duration = 0;
                    piece.entering = false;
                }

                this.currentGrid = [...grid];
            },

            async popMultiplierBombs(bombs, finalGrid) {
                if (!bombs || bombs.length === 0) return;

                const bombPositions = bombs.map((bomb) => bomb.position);
                for (const bomb of bombs) {
                    this.symbolPopups[bomb.position] = `x${bomb.multiplier}`;
                }

                await this.sleep(180);

                for (const piece of this.symbolPieces) {
                    piece.popping = bombPositions.includes(this.positionFromPiece(piece));
                }

                await this.sleep(270);

                for (const bomb of bombs) {
                    this.symbolPopups[bomb.position] = null;
                }

                const blankGrid = [...this.currentGrid];
                for (const pos of bombPositions) {
                    blankGrid[pos] = null;
                }

                this.symbolPieces = this.symbolPieces.filter((piece) => !piece.popping);
                this.currentGrid = blankGrid;
                await this.sleep(90);

                const settledGrid = this.collapseBombColumns(finalGrid, bombPositions);
                const affectedColumns = new Set(bombPositions.map((pos) => pos % 6));
                await this.animateCascadeToGrid(blankGrid, settledGrid, affectedColumns);
                this.currentGrid = settledGrid;
            },

            collapseBombColumns(grid, bombPositions) {
                const result = [...grid];
                const bombSet = new Set(bombPositions);

                for (const pos of bombPositions) {
                    result[pos] = null;
                }

                for (const col of new Set(bombPositions.map((pos) => pos % 6))) {
                    const columnSymbols = [];

                    for (let row = 4; row >= 0; row--) {
                        const pos = row * 6 + col;
                        if (!bombSet.has(pos) && grid[pos] !== null) {
                            columnSymbols.push(grid[pos]);
                        }
                    }

                    for (let row = 4; row >= 0; row--) {
                        const pos = row * 6 + col;
                        const index = 4 - row;
                        result[pos] = columnSymbols[index] ?? null;
                    }
                }

                return result;
            },

            nextFrame() {
                return new Promise(resolve => {
                    requestAnimationFrame(() => requestAnimationFrame(resolve));
                });
            },

            forcePaint() {
                return new Promise(resolve => {
                    requestAnimationFrame(() => {
                        if (this.$refs?.boardGrid) {
                            void this.$refs.boardGrid.getBoundingClientRect();
                        }
                        requestAnimationFrame(resolve);
                    });
                });
            },

            async handleFSTransitions(triggered, finished, bonusTotal, remaining) {
                if (triggered) {
                    // Pause autoplay but preserve the remaining count
                    if (this.autoplay) {
                        this.pendingAutoplayActive = true;
                        this.pendingAutoplayRemaining = this.autoplayRemaining;
                        this.autoplay = false;
                    }

                    await this.sleep(400);
                    this.showFSIntro = true;

                    if (this.fsAutoStartTimer) {
                        clearTimeout(this.fsAutoStartTimer);
                    }

                    this.fsAutoStartTimer = setTimeout(() => {
                        if (this.showFSIntro && !this.inFSMode) {
                            this.startFreeSpins(remaining, bonusTotal);
                        }
                    }, 900);
                } else if (finished) {
                    await this.sleep(500);
                    this.fsWinTotal = bonusTotal;
                    this.showFSOutro = true;

                    setTimeout(() => {
                        if (this.showFSOutro) {
                            this.closeFreeSpinsOutro();
                        }
                    }, 2000);
                }
            },

            startFreeSpins(remaining = this.fsRemaining, bonusTotal = this.fsWinTotal) {
                if (this.fsAutoStartTimer) {
                    clearTimeout(this.fsAutoStartTimer);
                }

                this.showFSIntro = false;
                this.inFSMode = true;
                this.fsRemaining = remaining;
                this.fsWinTotal = bonusTotal;

                // Spin the first Free Spin
                setTimeout(() => {
                    this.triggerSpin();
                }, 300);
            },

            closeFreeSpinsOutro() {
                this.showFSOutro = false;
                this.inFSMode = false;
                this.fsWinTotal = 0;
                this.fsRemaining = 0;

                if (this.pendingAutoplayActive && this.pendingAutoplayRemaining > 0) {
                    this.autoplay = true;
                    this.autoplayRemaining = this.pendingAutoplayRemaining;
                    this.pendingAutoplayActive = false;
                    this.pendingAutoplayRemaining = 0;
                    this.triggerSpin();
                }
            },

            async continueAutoplayIfNeeded() {
                if (this.showFSIntro || this.showFSOutro) return;

                // If we are in Free Spins, it operates like a continuous autoplay until spins = 0
                if (this.inFSMode && this.fsRemaining > 0) {
                    await this.sleep(400);
                    this.triggerSpin();
                    return;
                }

                // Standard autoplay continuation
                if (this.autoplay && this.autoplayRemaining > 0) {
                    await this.sleep(300);
                    this.autoplayRemaining--;
                    this.triggerSpin();
                } else {
                    this.stopAutoplay();
                }
            },

            dispatchToast(message, type) {
                window.dispatchEvent(new CustomEvent('show-toast', {
                    detail: {
                        message,
                        type
                    }
                }));
            },

            async animateCounter(prop, target, duration) {
                const start = this[prop];
                const diff = target - start;
                const startTime = performance.now();

                return new Promise(resolve => {
                    const step = (now) => {
                        const progress = Math.min((now - startTime) / duration, 1);
                        const eased = 1 - Math.pow(1 - progress, 3);
                        this[prop] = Math.round(start + diff * eased);

                        if (progress < 1) {
                            requestAnimationFrame(step);
                        } else {
                            this[prop] = target;
                            resolve();
                        }
                    };

                    requestAnimationFrame(step);
                });
            },

            sleep(ms) {
                return new Promise(resolve => setTimeout(resolve, ms));
            },
        }));
    </script>
@endscript
