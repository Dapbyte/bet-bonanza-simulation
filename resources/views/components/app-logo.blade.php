@props([
    'sidebar' => false,
])

{{-- Mahesa99 logo — used in Flux sidebar/brand slots --}}
@if($sidebar)
    <div {{ $attributes->merge(['class' => 'flex items-center gap-2']) }}>
        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-yellow-400 via-amber-500 to-orange-500 shadow-lg shadow-yellow-500/30 text-xl">
            🎰
        </div>
        <span class="text-lg font-black bg-gradient-to-r from-yellow-400 via-amber-400 to-orange-500 bg-clip-text text-transparent tracking-tight">
            MAHESA99
        </span>
    </div>
@else
    <div {{ $attributes->merge(['class' => 'flex items-center gap-2']) }}>
        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-yellow-400 via-amber-500 to-orange-500 shadow-lg shadow-yellow-500/30 text-xl">
            🎰
        </div>
        <span class="text-lg font-black bg-gradient-to-r from-yellow-400 via-amber-400 to-orange-500 bg-clip-text text-transparent tracking-tight">
            MAHESA99
        </span>
    </div>
@endif
