<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class PlayerList extends Component
{
    use WithPagination;

    public string $search = '';

    // For inline balance editing
    public ?int $editingPlayerId = null;
    public int $editBalance = 0;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Start editing a player's balance.
     */
    public function editPlayer(int $id): void
    {
        $player = User::where('role', 'player')->findOrFail($id);
        $this->editingPlayerId = $id;
        $this->editBalance = $player->balance;
    }

    /**
     * Save updated balance for a player.
     */
    public function updateBalance(): void
    {
        if ($this->editingPlayerId === null) return;

        $player = User::where('role', 'player')->findOrFail($this->editingPlayerId);

        if ($this->editBalance < 0) {
            $this->editBalance = 0;
        }

        $player->balance = $this->editBalance;
        $player->save();

        $this->editingPlayerId = null;
        $this->editBalance = 0;
    }

    /**
     * Cancel editing.
     */
    public function cancelEdit(): void
    {
        $this->editingPlayerId = null;
        $this->editBalance = 0;
    }

    public function render()
    {
        $players = User::where('role', 'player')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.admin.player-list', [
            'players' => $players,
        ]);
    }
}
