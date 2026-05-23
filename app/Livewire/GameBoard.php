<?php

namespace App\Livewire;

use App\Models\GameSetting;
use App\Models\SpinLog;
use App\Services\SlotEngine;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.game')]
class GameBoard extends Component
{
    // Grid state
    public array $grid = [];

    // Player state
    public int $credit = 0;
    public int $bet = 1;
    public int $coinValue = 100;
    public int $wins = 0;

    // Spin state
    public bool $isSpinning = false;
    public bool $autoplay = false;
    public int $autoplayRemaining = 0;
    public int $spinCount = 0;

    // Free Spins state (Sweet Bonanza Bonus Game)
    public bool $inFreeSpins = false;
    public int $freeSpinsRemaining = 0;
    public int $totalFreeSpinWin = 0;

    // Tumble tracking
    public int $tumbleStep = 0;
    public array $tumbleResults = [];

    public function mount(): void
    {
        $user = Auth::user();
        $this->credit = $user->balance;

        // Initialize grid with random symbols (display only)
        $engine = new SlotEngine();
        $this->grid = $engine->generateGrid(false);
    }

    /**
     * Computed property: total bet amount
     */
    public function getTotalBetProperty(): int
    {
        return $this->bet * $this->coinValue;
    }

    /**
     * Start autoplay with a given number of spins.
     */
    public function startAutoplay(int $count): void
    {
        if ($this->isSpinning) return;

        $this->autoplay = true;
        $this->autoplayRemaining = $count;
        $this->spin($this->bet, $this->coinValue);
    }

    /**
     * Stop autoplay after current spin.
     */
    public function stopAutoplay(): void
    {
        $this->autoplay = false;
        $this->autoplayRemaining = 0;
    }

    /**
     * Main spin action — authentic Sweet Bonanza logic.
     */
    public function spin(int $clientBet, int $clientCoinValue): void
    {
        if ($this->isSpinning) return;

        $wasInFreeSpins = $this->inFreeSpins;

        // Update local variables from client parameters (prevents lag on change)
        $this->bet = max(1, min(10, $clientBet));
        if (in_array($clientCoinValue, [100, 200, 500])) {
            $this->coinValue = $clientCoinValue;
        }

        $totalBet = $this->totalBet;

        // Deduct bet if NOT in Free Spins
        if (! $wasInFreeSpins) {
            if ($this->credit < $totalBet) {
                $this->dispatch('show-toast', message: 'Saldo tidak cukup!', type: 'error');
                $this->stopAutoplay();
                return;
            }

            // Deduct balance
            $user = Auth::user();
            $user->balance -= $totalBet;
            $user->save();
            $this->credit = $user->balance;
        } else {
            // Free Spin: decrement count
            $this->freeSpinsRemaining--;
        }

        $this->isSpinning = true;
        $this->wins = 0;
        $this->tumbleStep = 0;
        $this->tumbleResults = [];

        $this->dispatch('spin-started');

        // Generate initial grid (check if Free Spins is active)
        $engine = new SlotEngine();
        $this->spinCount++;

        $this->grid = $engine->generateGrid($this->inFreeSpins);

        // Force scatters if setting is triggered
        $scatterFreq = (int) GameSetting::getValue('scatter_frequency', 0);
        if ($scatterFreq > 0 && $this->spinCount % $scatterFreq === 0) {
            $engine->forceScatters($this->grid);
        }

        // Tumble evaluation variables
        $totalBaseWin = 0; // Win before multipliers
        $allSymbolsHit = [];
        $tumbleData = [];
        $scatterCount = 0;

        $currentGrid = $this->grid;
        $tumbleIndex = 0;

        do {
            $wins = $engine->evaluateGrid($currentGrid);
            if (empty($wins)) break;

            $tumbleWin = 0;
            $breakdown = [];

            foreach ($wins as $win) {
                $tumbleWin += $win['payout'];

                if ($win['symbol'] === 'scatter') {
                    $scatterCount = max($scatterCount, $win['count']);
                }

                $symbolInfo = SlotEngine::getSymbolInfo($win['symbol']);
                $breakdown[] = [
                    'symbol' => $win['symbol'],
                    'emoji' => $symbolInfo['emoji'],
                    'name' => $symbolInfo['name'],
                    'count' => $win['count'],
                    'payout' => $win['payout'],
                    'positions' => $win['positions'],
                ];

                $allSymbolsHit[] = [
                    'symbol' => $win['symbol'],
                    'count' => $win['count'],
                    'payout' => $win['payout'],
                ];
            }

            $totalBaseWin += $tumbleWin;

            // Prepare for next gravity cascade/tumble
            $nextGrid = $currentGrid;
            if ($engine->isTumbleEnabled()) {
                $nextGrid = $engine->applyTumble($currentGrid, $wins, $this->inFreeSpins);
            }

            $tumbleData[] = [
                'grid' => $currentGrid,
                'nextGrid' => $nextGrid,
                'wins' => $breakdown,
                'subtotal' => $tumbleWin,
                'tumbleIndex' => $tumbleIndex,
                'baseTotalSoFar' => $totalBaseWin,
            ];

            if ($engine->isTumbleEnabled()) {
                $currentGrid = $nextGrid;
                $tumbleIndex++;
            } else {
                break;
            }
        } while (true);

        // Update final grid state
        $this->grid = $currentGrid;

        // Calculate Multiplier Bombs visible on final grid (Only in Free Spins)
        $multiplierSum = 0;
        $bombs = [];
        if ($this->inFreeSpins && $totalBaseWin > 0) {
            $bombs = $engine->getVisibleMultiplierBombs($this->grid);
            foreach ($bombs as $bomb) {
                $multiplierSum += $bomb['multiplier'];
            }
        }

        // Total win of the spin
        $multiplierApplied = max(1, $multiplierSum);
        $finalWin = $totalBaseWin * $multiplierApplied;
        $this->wins = $finalWin;
        $this->tumbleResults = $tumbleData;

        // Check for Free Spins Trigger / Re-trigger (4+ scatters triggers FS, 3+ scatters in FS re-triggers)
        $freeSpinsTriggered = false;
        $freeSpinsReTriggered = false;

        if (! $this->inFreeSpins && $scatterCount >= 4) {
            $this->inFreeSpins = true;
            $this->freeSpinsRemaining = 10;
            $this->totalFreeSpinWin = 0;
            $freeSpinsTriggered = true;
        } elseif ($this->inFreeSpins && $scatterCount >= 3) {
            $this->freeSpinsRemaining += 5;
            $freeSpinsReTriggered = true;
        }

        // Credit winnings to balance
        $user = Auth::user();
        if ($finalWin > 0) {
            $user->refresh();
            $user->balance += $finalWin;
            $user->save();
            $this->credit = $user->balance;

            if ($wasInFreeSpins) {
                $this->totalFreeSpinWin += $finalWin;
            }
        }

        // Log spin
        SpinLog::create([
            'user_id' => $user->id,
            'bet_amount' => $wasInFreeSpins ? 0 : $totalBet,
            'win_amount' => $finalWin,
            'multiplier_reached' => $multiplierApplied,
            'symbols_hit' => $allSymbolsHit,
            'created_at' => now(),
        ]);

        // Clean up Free Spins mode if finished
        $freeSpinsFinished = false;
        if ($this->inFreeSpins && $this->freeSpinsRemaining <= 0) {
            $freeSpinsFinished = true;
            $this->inFreeSpins = false;
        }

        $this->isSpinning = false;

        // Dispatch full results to Alpine
        $this->dispatch('spin-result', [
            'tumbles' => $tumbleData,
            'baseWin' => $totalBaseWin,
            'multiplierSum' => $multiplierSum,
            'finalWin' => $finalWin,
            'bombs' => $bombs,
            'finalGrid' => $this->grid,
            'credit' => $this->credit,
            'freeSpinsTriggered' => $freeSpinsTriggered,
            'freeSpinsReTriggered' => $freeSpinsReTriggered,
            'freeSpinsRemaining' => $this->freeSpinsRemaining,
            'totalFreeSpinWin' => $this->totalFreeSpinWin,
            'freeSpinsFinished' => $freeSpinsFinished,
        ]);

        // Autoplay logic (only continue if not triggering Free Spins, or let Alpine handle autoplay)
        if ($this->autoplay) {
            $this->autoplayRemaining--;
            // Stop if balance insufficient or no spins left
            if ($this->autoplayRemaining <= 0 || (! $this->inFreeSpins && $this->credit < $this->totalBet)) {
                $this->stopAutoplay();
            }
        }
    }

    /**
     * Continue autoplay (called from frontend after animation completes).
     */
    public function continueAutoplay(): void
    {
        if ($this->autoplay && $this->autoplayRemaining > 0) {
            $this->spin($this->bet, $this->coinValue);
        }
    }

    public function render()
    {
        return view('livewire.game-board');
    }
}
