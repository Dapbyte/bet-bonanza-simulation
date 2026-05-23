<?php

namespace Tests\Unit;

use App\Services\SlotEngine;
use Database\Seeders\GameSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlotEngineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed game settings to in-memory sqlite database for the test
        $this->seed(GameSettingsSeeder::class);
    }

    /**
     * Test grid generation structure.
     */
    public function test_grid_generation_structure(): void
    {
        $engine = new SlotEngine();
        $grid = $engine->generateGrid(false);

        $this->assertIsArray($grid);
        $this->assertCount(30, $grid);

        foreach ($grid as $symbol) {
            $this->assertNotNull($symbol);
            $this->assertArrayHasKey($symbol, SlotEngine::SYMBOLS);
        }
    }

    /**
     * Test Free Spins grid generation injects bombs.
     */
    public function test_free_spins_grid_contains_bombs(): void
    {
        $engine = new SlotEngine();
        
        // Since injection is random (25%), let's run it multiple times until we find bombs
        $foundBomb = false;
        for ($i = 0; $i < 50; $i++) {
            $grid = $engine->generateGrid(true);
            $bombs = $engine->getVisibleMultiplierBombs($grid);
            if (!empty($bombs)) {
                $foundBomb = true;
                break;
            }
        }
        $this->assertTrue($foundBomb, 'Grid should eventually contain multiplier bombs during Free Spins');
    }

    /**
     * Test evaluateGrid with no winning combinations.
     */
    public function test_evaluate_grid_no_wins(): void
    {
        $engine = new SlotEngine();
        // Construct a grid where no symbol has 8+ occurrences, and no scatter
        $grid = [
            'heart', 'purple_sq', 'green_pent', 'blue_oval', 'apple', 'plum',
            'heart', 'purple_sq', 'green_pent', 'blue_oval', 'apple', 'plum',
            'heart', 'purple_sq', 'green_pent', 'blue_oval', 'apple', 'plum',
            'heart', 'purple_sq', 'green_pent', 'blue_oval', 'apple', 'plum',
            'heart', 'purple_sq', 'green_pent', 'blue_oval', 'apple', 'plum',
        ];

        $wins = $engine->evaluateGrid($grid);
        $this->assertEmpty($wins);
    }

    /**
     * Test evaluateGrid with a winning combination (8 apples).
     */
    public function test_evaluate_grid_with_symbol_win(): void
    {
        $engine = new SlotEngine();
        // Construct a grid with 8 apples
        $grid = array_fill(0, 30, 'heart'); // Fill everything with hearts first
        // Replace 8 elements with apple
        for ($i = 0; $i < 8; $i++) {
            $grid[$i] = 'apple';
        }
        // Make sure hearts don't win by dispersing other symbols so hearts count is < 8
        for ($i = 8; $i < 30; $i++) {
            $grid[$i] = ($i % 2 === 0) ? 'banana' : 'plum';
        }

        // Now we have 8 apples, 11 bananas, 11 plums. Bananas and plums will win too, but we verify apple win structure.
        $wins = $engine->evaluateGrid($grid);

        $appleWin = collect($wins)->firstWhere('symbol', 'apple');
        $this->assertNotNull($appleWin);
        $this->assertEquals(8, $appleWin['count']);
        $this->assertCount(8, $appleWin['positions']);
    }

    /**
     * Test evaluateGrid with scatter win (4 scatters).
     */
    public function test_evaluate_grid_with_scatter_win(): void
    {
        $engine = new SlotEngine();
        $grid = array_fill(0, 30, 'banana');
        // We have 26 bananas (which will win) and 4 scatters
        $grid[0] = 'scatter';
        $grid[5] = 'scatter';
        $grid[12] = 'scatter';
        $grid[25] = 'scatter';

        $wins = $engine->evaluateGrid($grid);
        $scatterWin = collect($wins)->firstWhere('symbol', 'scatter');
        $this->assertNotNull($scatterWin);
        $this->assertEquals(4, $scatterWin['count']);
        $this->assertEquals(SlotEngine::SCATTER_PAYOUTS[4], $scatterWin['payout']);
    }

    /**
     * Test getting and summing visible multiplier bombs.
     */
    public function test_get_visible_multiplier_bombs(): void
    {
        $engine = new SlotEngine();
        $grid = array_fill(0, 30, 'apple');
        // Place some bombs
        $grid[3] = 'bomb_10';
        $grid[14] = 'bomb_25';

        $bombs = $engine->getVisibleMultiplierBombs($grid);
        $this->assertCount(2, $bombs);
        
        $multiplierSum = array_sum(array_column($bombs, 'multiplier'));
        $this->assertEquals(35, $multiplierSum); // 10 + 25 = 35
    }
}
