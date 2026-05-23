<?php

use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\PlayerList;
use App\Livewire\GameBoard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Landing page - redirect to appropriate section
Route::get('/', function () {
    if (Auth::check()) {
        return Auth::user()->isAdmin()
            ? redirect('/admin')
            : redirect('/game');
    }
    return redirect('/login');
})->name('home');

// Player routes
Route::middleware(['auth', 'role:player'])->group(function () {
    Route::get('/game', GameBoard::class)->name('game');
});

// Admin routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/', Dashboard::class)->name('admin.dashboard');
    Route::get('/players', PlayerList::class)->name('admin.players');
});

// Post-login redirect (overrides Fortify's /dashboard default)
Route::middleware(['auth'])->get('/dashboard', function () {
    return Auth::user()->isAdmin()
        ? redirect('/admin')
        : redirect('/game');
})->name('dashboard');

require __DIR__.'/settings.php';
