<?php

namespace App\Livewire\Admin;

use App\Models\GameSetting;
use App\Models\User;
use App\Models\UserSpinSetting;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Dashboard extends Component
{
    // Symbol chances
    public array $symbolChances = [];

    // Multipliers (payout table)
    public array $multipliers = [];

    // Other settings
    public $scatterFrequency = 0;
    public $defaultBalance = 500000;
    public bool $tumbleEnabled = true;
    public $maxMultiplier = 10;

    // Per-user probability settings
    public array $players = [];
    public ?int $selectedUserId = null;
    public bool $userSettingsEnabled = false;
    public array $userSymbolRates = [];
    public int $userScatterRate = 3;
    public int $userBombRate = 8;
    public int $userSpinWindow = 100;
    public int $userWindowSpinCount = 0;
    public string $userSaveMessage = '';


    // UI state
    public string $saveMessage = '';

    public function mount(): void
    {
        $this->symbolChances = GameSetting::getJson('symbol_chances', [
            'heart' => 10, 'purple_sq' => 10, 'green_pent' => 10,
            'blue_oval' => 10, 'apple' => 12, 'plum' => 12,
            'watermelon' => 12, 'grape' => 12, 'banana' => 12,
        ]);

        $this->multipliers = GameSetting::getJson('multipliers', []);

        $this->scatterFrequency = (int) GameSetting::getValue('scatter_frequency', 0);
        $this->defaultBalance = (int) GameSetting::getValue('default_balance', 500000);
        $this->tumbleEnabled = (bool) GameSetting::getValue('tumble_enabled', 1);
        $this->maxMultiplier = (int) GameSetting::getValue('max_multiplier', 10);

        $this->players = User::query()
            ->where('role', 'player')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();
        $this->selectedUserId = $this->players[0]['id'] ?? null;
        $this->loadUserSettings();

    }

    /**
     * Get the total of symbol chances for validation display.
     */
    public function getChancesTotalProperty(): int
    {
        return array_sum($this->sanitizeIntArray($this->symbolChances));
    }

    public function updatedSymbolChances($value, string $key): void
    {
        $this->symbolChances[$key] = $this->sanitizeInt($value);
    }

    public function updatedMultipliers($value, string $key): void
    {
        data_set($this->multipliers, $key, $this->sanitizeInt($value));
    }

    public function updatedScatterFrequency($value): void
    {
        $this->scatterFrequency = $this->sanitizeInt($value);
    }

    public function updatedDefaultBalance($value): void
    {
        $this->defaultBalance = $this->sanitizeInt($value);
    }

    public function updatedMaxMultiplier($value): void
    {
        $this->maxMultiplier = $this->sanitizeInt($value);
    }

    public function updatedSelectedUserId(): void
    {
        $this->loadUserSettings();
    }

    public function updatedUserSymbolRates($value, string $key): void
    {
        $this->userSymbolRates[$key] = $this->sanitizePercent($value);
    }

    public function updatedUserScatterRate($value): void
    {
        $this->userScatterRate = $this->sanitizePercent($value);
    }

    public function updatedUserBombRate($value): void
    {
        $this->userBombRate = $this->sanitizePercent($value);
    }

    public function updatedUserSpinWindow($value): void
    {
        $this->userSpinWindow = max(1, $this->sanitizeInt($value));
    }

    public function getUserSymbolRatesTotalProperty(): int
    {
        return array_sum($this->sanitizePercentArray($this->userSymbolRates));
    }

    public function getUserTotalRateProperty(): int
    {
        return $this->userSymbolRatesTotal + $this->userScatterRate + $this->userBombRate;
    }

    public function loadUserSettings(): void
    {
        $this->userSaveMessage = '';
        if (! $this->selectedUserId) {
            $this->userSymbolRates = [];
            $this->userScatterRate = 3;
            $this->userBombRate = 8;
            $this->userSpinWindow = 100;
            $this->userWindowSpinCount = 0;
            $this->userSettingsEnabled = false;
            return;
        }

        $setting = UserSpinSetting::forUser($this->selectedUserId);
        $this->userSymbolRates = $this->sanitizePercentArray($setting->symbol_rates ?? []);
        $this->userScatterRate = $this->sanitizePercent($setting->scatter_rate ?? 3);
        $this->userBombRate = $this->sanitizePercent($setting->bomb_rate ?? 8);
        $this->userSpinWindow = max(1, $this->sanitizeInt($setting->spin_window_size ?? 100));
        $this->userWindowSpinCount = (int) ($setting->window_spin_count ?? 0);
        $this->userSettingsEnabled = (bool) ($setting->is_enabled ?? false);
    }

    public function saveUserSettings(): void
    {
        if (! $this->selectedUserId) {
            $this->userSaveMessage = 'Error: Please select a user.';
            return;
        }

        $this->userSymbolRates = $this->sanitizePercentArray($this->userSymbolRates);
        $this->userScatterRate = $this->sanitizePercent($this->userScatterRate);
        $this->userBombRate = $this->sanitizePercent($this->userBombRate);
        $this->userSpinWindow = max(1, $this->sanitizeInt($this->userSpinWindow));

        if ($this->userSettingsEnabled && ($this->userScatterRate + $this->userBombRate) > 100) {
            $this->userSaveMessage = 'Error: Scatter + Bomb rate cannot exceed 100%.';
            return;
        }

        $setting = UserSpinSetting::forUser($this->selectedUserId);
        $setting->is_enabled = $this->userSettingsEnabled;
        $setting->spin_window_size = $this->userSpinWindow;
        $setting->symbol_rates = $this->userSymbolRates;
        $setting->scatter_rate = $this->userScatterRate;
        $setting->bomb_rate = $this->userBombRate;
        $setting->window_spin_count = 0;
        $setting->window_symbol_counts = [];
        $setting->window_scatter_count = 0;
        $setting->window_bomb_count = 0;
        $setting->save();

        $this->userWindowSpinCount = $setting->window_spin_count;
        $this->userSaveMessage = 'User probability settings saved successfully!';
    }

    /**
     * Save all settings to database.
     */
    public function save(): void
    {
        $this->symbolChances = $this->sanitizeIntArray($this->symbolChances);
        $this->multipliers = $this->sanitizeIntArray($this->multipliers);
        $this->scatterFrequency = $this->sanitizeInt($this->scatterFrequency);
        $this->defaultBalance = $this->sanitizeInt($this->defaultBalance);
        $this->maxMultiplier = $this->sanitizeInt($this->maxMultiplier);

        // Validate all weights are non-negative. They do not need to total 100.
        foreach ($this->symbolChances as $symbol => $chance) {
            if ($chance < 0) {
                $this->saveMessage = 'Error: Symbol weights cannot be negative';
                return;
            }
        }

        // Validate multiplier values
        if ($this->maxMultiplier < 1) {
            $this->saveMessage = "Error: Max multiplier must be at least 1";
            return;
        }

        if ($this->defaultBalance < 0) {
            $this->saveMessage = "Error: Default balance cannot be negative";
            return;
        }



        // Save all settings
        GameSetting::setValue('symbol_chances', $this->symbolChances);
        GameSetting::setValue('multipliers', $this->multipliers);
        GameSetting::setValue('scatter_frequency', $this->scatterFrequency);
        GameSetting::setValue('default_balance', $this->defaultBalance);
        GameSetting::setValue('tumble_enabled', $this->tumbleEnabled ? '1' : '0');
        GameSetting::setValue('max_multiplier', $this->maxMultiplier);


        $this->saveMessage = 'Settings saved successfully!';
    }

    private function sanitizeInt($value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }

        if (! is_numeric($value)) {
            return 0;
        }

        return max(0, (int) $value);
    }

    private function sanitizePercent($value): int
    {
        return min(100, $this->sanitizeInt($value));
    }

    private function sanitizePercentArray(array $values): array
    {
        foreach ($values as $key => $value) {
            $values[$key] = is_array($value)
                ? $this->sanitizePercentArray($value)
                : $this->sanitizePercent($value);
        }

        return $values;
    }

    private function sanitizeIntArray(array $values): array
    {
        foreach ($values as $key => $value) {
            $values[$key] = is_array($value)
                ? $this->sanitizeIntArray($value)
                : $this->sanitizeInt($value);
        }

        return $values;
    }

    public function render()
    {
        return view('livewire.admin.dashboard');
    }
}
