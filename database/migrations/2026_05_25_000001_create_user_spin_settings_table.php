<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_spin_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('is_enabled')->default(false);
            $table->unsignedInteger('spin_window_size')->default(100);
            $table->json('symbol_rates')->nullable();
            $table->unsignedInteger('scatter_rate')->default(3);
            $table->unsignedInteger('bomb_rate')->default(8);
            $table->unsignedInteger('window_spin_count')->default(0);
            $table->json('window_symbol_counts')->nullable();
            $table->unsignedInteger('window_scatter_count')->default(0);
            $table->unsignedInteger('window_bomb_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_spin_settings');
    }
};
