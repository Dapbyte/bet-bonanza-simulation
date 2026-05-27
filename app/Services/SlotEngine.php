<?php

namespace App\Services;

use App\Models\GameSetting;
use App\Models\UserSpinSetting;

class SlotEngine
{
    // Symbol display names and emoji icons
    public const SYMBOLS = [
        'heart'      => ['name' => 'Heart',      'emoji' => '❤️'],
        'purple_sq'  => ['name' => 'Purple Sq',   'emoji' => '🟪'],
        'green_pent' => ['name' => 'Green Pent',  'emoji' => '💚'],
        'blue_oval'  => ['name' => 'Blue Oval',   'emoji' => '🔵'],
        'apple'      => ['name' => 'Apple',       'emoji' => '🍎'],
        'plum'       => ['name' => 'Plum',        'emoji' => '🍑'],
        'watermelon' => ['name' => 'Watermelon',  'emoji' => '🍉'],
        'grape'      => ['name' => 'Grape',       'emoji' => '🍇'],
        'banana'     => ['name' => 'Banana',      'emoji' => '🍌'],
        'scatter'    => ['name' => 'Scatter',     'emoji' => '🍭'],

        // Multiplier bomb symbols (only appear in Free Spins)
        'bomb_2'     => ['name' => 'Bomb 2x',     'emoji' => '💣', 'multiplier' => 2],
        'bomb_5'     => ['name' => 'Bomb 5x',     'emoji' => '💣', 'multiplier' => 5],
        'bomb_10'    => ['name' => 'Bomb 10x',    'emoji' => '💣', 'multiplier' => 10],
        'bomb_25'    => ['name' => 'Bomb 25x',    'emoji' => '💣', 'multiplier' => 25],
        'bomb_50'    => ['name' => 'Bomb 50x',    'emoji' => '💣', 'multiplier' => 50],
        'bomb_100'   => ['name' => 'Bomb 100x',   'emoji' => '💣', 'multiplier' => 100],
    ];

    // Scatter payouts
    public const SCATTER_PAYOUTS = [
        4 => 3000,
        5 => 5000,
        6 => 100000,
    ];

    protected array $symbolChances;
    protected array $multipliers;
    protected int $scatterFrequency;
    protected bool $tumbleEnabled;
    protected int $maxMultiplier;
    protected int $bombChance; // Chance out of 100 for a bomb to appear in Free Spins

    protected ?UserSpinSetting $userSetting = null;
    protected array $userSymbolRates = [];
    protected int $userSpinWindowSize = 0;
    protected int $userWindowSpinCount = 0;
    protected array $userWindowSymbolCounts = [];
    protected int $userWindowScatterCount = 0;
    protected int $userWindowBombCount = 0;
    protected int $userScatterRate = 0;
    protected int $userBombRate = 0;

    public function __construct(?UserSpinSetting $userSetting = null)
    {
        $this->userSetting = $userSetting;
        $this->loadSettings();
    }

    /**
     * Load game settings from database.
     */
    public function loadSettings(): void
    {
        $this->symbolChances = GameSetting::getJson('symbol_chances', [
            'heart' => 10, 'purple_sq' => 10, 'green_pent' => 10,
            'blue_oval' => 10, 'apple' => 12, 'plum' => 12,
            'watermelon' => 12, 'grape' => 12, 'banana' => 12,
        ]);
        $this->symbolChances = $this->filterBombWeights($this->symbolChances);

        $this->multipliers = GameSetting::getJson('multipliers', []);
        $this->scatterFrequency = (int) GameSetting::getValue('scatter_frequency', 0);
        $this->tumbleEnabled = (bool) GameSetting::getValue('tumble_enabled', 1);
        $this->maxMultiplier = (int) GameSetting::getValue('max_multiplier', 100);
        $this->bombChance = (int) GameSetting::getValue('multiplier_bomb_chance', 8); // Default 8% chance per position in Free Spins

        if ($this->userSetting) {
            $this->userSymbolRates = $this->sanitizeRateArray($this->userSetting->symbol_rates ?? []);
            $this->userSpinWindowSize = max(1, (int) $this->userSetting->spin_window_size);
            $this->userWindowSpinCount = max(0, (int) $this->userSetting->window_spin_count);
            $this->userWindowSymbolCounts = $this->sanitizeRateArray($this->userSetting->window_symbol_counts ?? []);
            $this->userWindowScatterCount = max(0, (int) $this->userSetting->window_scatter_count);
            $this->userWindowBombCount = max(0, (int) $this->userSetting->window_bomb_count);
            $this->userScatterRate = max(0, (int) $this->userSetting->scatter_rate);
            $this->userBombRate = max(0, (int) $this->userSetting->bomb_rate);
        }
    }

    /**
     * Generate a 6x5 grid (30 positions) of symbols.
     */
    public function generateGrid(bool $inFreeSpins = false): array
    {
        if ($this->usesUserSpinSettings()) {
            $grid = $this->generateGridWithUserRates($inFreeSpins);
            $this->sanitizeBombs($grid, $inFreeSpins);
            return $grid;
        }

        $grid = [];

        for ($i = 0; $i < 30; $i++) {
            $grid[$i] = $this->weightedRandom($this->symbolChances);
        }

        // Inject scatter symbols
        $this->injectScatters($grid);

        // Inject multiplier bombs only in Free Spins
        if ($inFreeSpins) {
            $this->injectBombs($grid);
        }

        $this->sanitizeBombs($grid, $inFreeSpins);

        return $grid;
    }

    public function usesUserSpinSettings(): bool
    {
        return $this->userSetting !== null && (bool) $this->userSetting->is_enabled;
    }

    protected function generateGridWithUserRates(bool $inFreeSpins = false): array
    {
        $this->resetUserWindowIfNeeded();

        $windowSize = max(1, $this->userSpinWindowSize);
        $windowTotalDraws = $windowSize * 30;

        $symbolRates = $this->getNormalizedSymbolRates();
        $symbolCounts = $this->userWindowSymbolCounts;
        $scatterCount = $this->userWindowScatterCount;
        $bombCount = $this->userWindowBombCount;

        $grid = [];

        for ($i = 0; $i < 30; $i++) {
            $drawsSoFar = $this->getTotalWindowDraws($symbolCounts, $scatterCount, $bombCount);
            $remainingDraws = max(1, $windowTotalDraws - $drawsSoFar);

            if ($this->shouldPlaceScatter($windowTotalDraws, $scatterCount, $remainingDraws)) {
                $grid[$i] = 'scatter';
                $scatterCount++;
                continue;
            }

            if ($inFreeSpins && $this->shouldPlaceBomb($windowTotalDraws, $bombCount, $remainingDraws)) {
                $grid[$i] = $this->getRandomBombSymbol();
                $bombCount++;
                continue;
            }

            $weights = $this->getWindowAdjustedWeights($symbolRates, $windowTotalDraws, $symbolCounts);
            $symbol = $this->weightedRandom($weights);
            $grid[$i] = $symbol;
            $symbolCounts[$symbol] = ($symbolCounts[$symbol] ?? 0) + 1;
        }

        $this->userWindowSymbolCounts = $symbolCounts;
        $this->userWindowScatterCount = $scatterCount;
        $this->userWindowBombCount = $bombCount;
        $this->userWindowSpinCount++;

        $this->persistUserWindowCounts();

        return $grid;
    }

    protected function getNormalizedSymbolRates(): array
    {
        $baseWeights = $this->symbolChances;
        $symbols = array_keys($baseWeights);

        $scatterRate = max(0, $this->userScatterRate);
        $bombRate = max(0, $this->userBombRate);
        $budget = max(0.0, 100.0 - $scatterRate - $bombRate);

        $provided = [];
        foreach ($symbols as $symbol) {
            if (array_key_exists($symbol, $this->userSymbolRates)) {
                $provided[$symbol] = max(0.0, (float) $this->userSymbolRates[$symbol]);
            }
        }

        $normalized = [];
        if ($budget <= 0.0) {
            foreach ($symbols as $symbol) {
                $normalized[$symbol] = 0.0;
            }
            return $normalized;
        }

        $providedTotal = array_sum($provided);
        $remaining = $budget;

        if ($providedTotal > 0.0) {
            $scale = min(1.0, $budget / $providedTotal);
            foreach ($provided as $symbol => $rate) {
                $normalized[$symbol] = $rate * $scale;
            }
            $remaining = $budget - array_sum($normalized);
        }

        $missing = array_diff($symbols, array_keys($normalized));
        if (! empty($missing) && $remaining > 0.0) {
            $missingBaseTotal = array_sum(array_intersect_key($baseWeights, array_flip($missing)));
            if ($missingBaseTotal > 0) {
                foreach ($missing as $symbol) {
                    $normalized[$symbol] = ($baseWeights[$symbol] / $missingBaseTotal) * $remaining;
                }
                $remaining = 0.0;
            }
        }

        if ($remaining > 0.0) {
            $currentTotal = array_sum($normalized);
            if ($currentTotal > 0) {
                foreach ($normalized as $symbol => $rate) {
                    $normalized[$symbol] = $rate + ($rate / $currentTotal) * $remaining;
                }
            } else {
                $baseTotal = array_sum($baseWeights);
                if ($baseTotal > 0) {
                    foreach ($symbols as $symbol) {
                        $normalized[$symbol] = ($baseWeights[$symbol] / $baseTotal) * $remaining;
                    }
                }
            }
        }

        foreach ($symbols as $symbol) {
            $normalized[$symbol] = $normalized[$symbol] ?? 0.0;
        }

        return $normalized;
    }

    protected function getWindowAdjustedWeights(array $rates, int $windowTotalDraws, array $currentCounts): array
    {
        $weights = [];
        foreach ($rates as $symbol => $rate) {
            $target = $windowTotalDraws * ($rate / 100);
            $remaining = $target - ($currentCounts[$symbol] ?? 0);
            $weights[$symbol] = max(0.0, $remaining);
        }

        if (array_sum($weights) <= 0) {
            return $rates;
        }

        return $weights;
    }

    protected function shouldPlaceScatter(int $windowTotalDraws, int $currentCount, int $remainingDraws): bool
    {
        if ($this->userScatterRate <= 0) {
            return false;
        }

        $target = (int) round($windowTotalDraws * ($this->userScatterRate / 100));
        $remaining = $target - $currentCount;
        if ($remaining <= 0) {
            return false;
        }

        $chance = min(1.0, $remaining / max(1, $remainingDraws));
        return $this->randFloat() <= $chance;
    }

    protected function shouldPlaceBomb(int $windowTotalDraws, int $currentCount, int $remainingDraws): bool
    {
        if ($this->userBombRate <= 0) {
            return false;
        }

        $target = (int) round($windowTotalDraws * ($this->userBombRate / 100));
        $remaining = $target - $currentCount;
        if ($remaining <= 0) {
            return false;
        }

        $chance = min(1.0, $remaining / max(1, $remainingDraws));
        return $this->randFloat() <= $chance;
    }

    protected function resetUserWindowIfNeeded(): void
    {
        if (! $this->userSetting) {
            return;
        }

        $windowSize = max(1, $this->userSpinWindowSize);
        if ($this->userWindowSpinCount < $windowSize) {
            return;
        }

        $this->userWindowSpinCount = 0;
        $this->userWindowSymbolCounts = [];
        $this->userWindowScatterCount = 0;
        $this->userWindowBombCount = 0;

        $this->persistUserWindowCounts();
    }

    protected function persistUserWindowCounts(): void
    {
        if (! $this->userSetting) {
            return;
        }

        $this->userSetting->spin_window_size = $this->userSpinWindowSize;
        $this->userSetting->symbol_rates = $this->userSymbolRates;
        $this->userSetting->scatter_rate = $this->userScatterRate;
        $this->userSetting->bomb_rate = $this->userBombRate;
        $this->userSetting->window_spin_count = $this->userWindowSpinCount;
        $this->userSetting->window_symbol_counts = $this->userWindowSymbolCounts;
        $this->userSetting->window_scatter_count = $this->userWindowScatterCount;
        $this->userSetting->window_bomb_count = $this->userWindowBombCount;
        $this->userSetting->save();
    }

    protected function getTotalWindowDraws(array $symbolCounts, int $scatterCount, int $bombCount): int
    {
        return (int) array_sum($symbolCounts) + $scatterCount + $bombCount;
    }

    protected function percentRoll(float $percent): bool
    {
        return $this->randFloat() <= ($percent / 100);
    }

    protected function randFloat(): float
    {
        return mt_rand() / mt_getrandmax();
    }

    protected function sanitizeRateArray(array $values): array
    {
        foreach ($values as $key => $value) {
            $values[$key] = max(0, (int) $value);
        }

        return $values;
    }

    /**
     * Fill empty positions (null) with new random symbols.
     */
    public function fillEmptyPositions(array &$grid, bool $inFreeSpins = false): void
    {
        if ($this->usesUserSpinSettings()) {
            $symbolRates = $this->getNormalizedSymbolRates();
            for ($i = 0; $i < 30; $i++) {
                if ($grid[$i] === null) {
                    if ($this->userScatterRate > 0 && $this->percentRoll($this->userScatterRate)) {
                        $grid[$i] = 'scatter';
                        continue;
                    }

                    if ($inFreeSpins && $this->userBombRate > 0 && $this->percentRoll($this->userBombRate)) {
                        $grid[$i] = $this->getRandomBombSymbol();
                        continue;
                    }

                    $grid[$i] = $this->weightedRandom($symbolRates);
                }
            }

            $this->sanitizeBombs($grid, $inFreeSpins);
            return;
        }

        $allowBombs = $inFreeSpins;
        for ($i = 0; $i < 30; $i++) {
            if ($grid[$i] === null) {
                // Determine if we inject a bomb or regular symbol
                if ($allowBombs && rand(1, 100) <= $this->bombChance) {
                    $grid[$i] = $this->getRandomBombSymbol();
                } else {
                    $grid[$i] = $this->weightedRandom($this->symbolChances);

                    // Small chance for scatter in new symbols
                    if (rand(1, 100) <= 2) {
                        $grid[$i] = 'scatter';
                    }
                }
            }
        }

        $this->sanitizeBombs($grid, $inFreeSpins);
    }

    /**
     * Evaluate grid for winning combinations.
     *
     * @return array Array of wins: [{symbol, count, payout, positions}]
     */
    public function evaluateGrid(array $grid): array
    {
        $wins = [];
        $symbolCounts = [];
        $symbolPositions = [];

        foreach ($grid as $pos => $symbol) {
            if ($symbol === null) continue;

            $symbolCounts[$symbol] ??= 0;
            $symbolPositions[$symbol] ??= [];
            $symbolCounts[$symbol]++;
            $symbolPositions[$symbol][] = $pos;
        }

        // Check scatter wins
        if (($symbolCounts['scatter'] ?? 0) >= 4) {
            $count = $symbolCounts['scatter'];
            $payout = $count >= 6
                ? self::SCATTER_PAYOUTS[6]
                : ($count >= 5 ? self::SCATTER_PAYOUTS[5] : self::SCATTER_PAYOUTS[4]);

            $wins[] = [
                'symbol' => 'scatter',
                'count' => $count,
                'payout' => $payout,
                'positions' => $symbolPositions['scatter'],
            ];
        }

        // Check regular symbol wins (8+ of same kind)
        foreach ($symbolCounts as $symbol => $count) {
            // Ignore scatter and bombs
            if ($symbol === 'scatter' || str_starts_with($symbol, 'bomb_') || $count < 8) {
                continue;
            }

            $tier = $count >= 12 ? '12' : ($count >= 10 ? '10' : '8');
            $payout = (int) ($this->multipliers[$symbol][$tier] ?? 0);

            $wins[] = [
                'symbol' => $symbol,
                'count' => $count,
                'payout' => $payout,
                'positions' => $symbolPositions[$symbol],
            ];
        }

        return $wins;
    }

    /**
     * Apply tumble: remove winning symbols, drop remaining down, fill from top.
     */
    public function applyTumble(array $grid, array $wins, bool $inFreeSpins = false): array
    {
        // Collect all winning positions
        $winPositions = [];
        foreach ($wins as $win) {
            foreach ($win['positions'] as $pos) {
                $winPositions[$pos] = true;
            }
        }

        // Remove winning symbols (bombs never pop, so they are not in winPositions)
        foreach (array_keys($winPositions) as $pos) {
            $grid[$pos] = null;
        }

        // Apply gravity: for each column, drop symbols down
        for ($col = 0; $col < 6; $col++) {
            $columnSymbols = [];
            for ($row = 4; $row >= 0; $row--) {
                $pos = $row * 6 + $col;
                if ($grid[$pos] !== null) {
                    $columnSymbols[] = $grid[$pos];
                }
            }

            for ($row = 4; $row >= 0; $row--) {
                $pos = $row * 6 + $col;
                $index = 4 - $row;
                $grid[$pos] = $columnSymbols[$index] ?? null;
            }
        }

        // Fill empty positions with new symbols
        $this->fillEmptyPositions($grid, $inFreeSpins);

        return $grid;
    }

    /**
     * Scan grid and sum up all visible multiplier bombs.
     */
    public function getVisibleMultiplierBombs(array $grid): array
    {
        $bombs = [];
        foreach ($grid as $pos => $symbol) {
            if ($symbol !== null && str_starts_with($symbol, 'bomb_')) {
                $multiplierValue = (int) (self::SYMBOLS[$symbol]['multiplier'] ?? 1);
                $bombs[] = [
                    'position' => $pos,
                    'symbol' => $symbol,
                    'multiplier' => $multiplierValue,
                ];
            }
        }
        return $bombs;
    }

    /**
     * Check if tumble is enabled.
     */
    public function isTumbleEnabled(): bool
    {
        return $this->tumbleEnabled;
    }

    /**
     * Get symbol info (name + emoji) for a symbol ID.
     */
    public static function getSymbolInfo(string $symbolId): array
    {
        return self::SYMBOLS[$symbolId] ?? ['name' => $symbolId, 'emoji' => '❓'];
    }

    /**
     * Weighted random selection from symbol chances.
     */
    protected function weightedRandom(array $weights): string
    {
        $total = array_sum($weights);
        if ($total <= 0) {
            return array_key_first($weights) ?: 'apple';
        }

        $rand = $this->randFloat() * $total;
        $cumulative = 0.0;

        foreach ($weights as $symbol => $weight) {
            $cumulative += max(0.0, (float) $weight);
            if ($rand <= $cumulative) {
                return $symbol;
            }
        }

        return array_key_first($weights) ?: 'apple';
    }

    /**
     * Inject scatter symbols into the grid.
     */
    protected function injectScatters(array &$grid): void
    {
        if ($this->scatterFrequency !== 0) {
            return;
        }

        // Random scatter: ~3% chance per cell
        for ($i = 0; $i < 30; $i++) {
            if (rand(1, 100) <= 3) {
                $grid[$i] = 'scatter';
            }
        }
    }

    /**
     * Inject multiplier bombs into the grid (used during Free Spins initialization).
     */
    protected function injectBombs(array &$grid): void
    {
        // 15% chance to land at least one bomb, up to 3 bombs
        if (rand(1, 100) <= 25) {
            $count = rand(1, 3);
            $positions = (array) array_rand(array_fill(0, 30, true), $count);
            foreach ($positions as $pos) {
                // Avoid overwriting scatters
                if ($grid[$pos] !== 'scatter') {
                    $grid[$pos] = $this->getRandomBombSymbol();
                }
            }
        }
    }

    /**
     * Check if the grid currently contains any scatter symbols.
     */
    protected function gridHasScatter(array $grid): bool
    {
        foreach ($grid as $symbol) {
            if ($symbol === 'scatter') {
                return true;
            }
        }

        return false;
    }

    /**
     * Remove bombs if not in Free Spins.
     */
    protected function sanitizeBombs(array &$grid, bool $inFreeSpins): void
    {
        if ($inFreeSpins) {
            return;
        }

        foreach ($grid as $i => $symbol) {
            if ($symbol !== null && str_starts_with($symbol, 'bomb_')) {
                $grid[$i] = $this->weightedRandom($this->filterBombWeights($this->symbolChances));
            }
        }
    }

    /**
     * Remove bomb symbols from a weight table.
     */
    protected function filterBombWeights(array $weights): array
    {
        foreach (array_keys($weights) as $symbol) {
            if (str_starts_with($symbol, 'bomb_')) {
                unset($weights[$symbol]);
            }
        }

        return $weights;
    }

    /**
     * Get a random multiplier bomb symbol based on weighted probabilities.
     */
    protected function getRandomBombSymbol(): string
    {
        $rand = rand(1, 100);
        if ($rand <= 60) return 'bomb_2';
        if ($rand <= 80) return 'bomb_5';
        if ($rand <= 90) return 'bomb_10';
        if ($rand <= 96) return 'bomb_25';
        if ($rand <= 99) return 'bomb_50';
        return 'bomb_100';
    }

    /**
     * Force scatter injection (used when scatter_frequency counter triggers).
     */
    public function forceScatters(array &$grid, ?int $count = null): void
    {
        $count = min($count ?? rand(4, 6), 30);
        $positions = (array) array_rand(array_fill(0, 30, true), $count);

        foreach ($positions as $pos) {
            $grid[$pos] = 'scatter';
        }
    }
}
