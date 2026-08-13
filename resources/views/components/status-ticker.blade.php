<div>
    <!-- Nothing in life is to be feared, it is only to be understood. Now is the time to understand more, so that we may fear less. - Maria Skłodowska-Curie -->
</div>
@props(['date', 'streakDays' => 0])

@php
    $dateLabel = $date->format('l, F j');
    $streakLabel = number_format($streakDays).' day streak';
@endphp

<div class="h-5 overflow-hidden text-xs font-medium text-stone-500">
    <span class="sr-only">{{ $dateLabel }}. {{ $streakLabel }}.</span>
    <div class="motion-safe:animate-status-ticker motion-reduce:animate-none" aria-hidden="true">
        <div class="flex h-5 items-center whitespace-nowrap">{{ $dateLabel }}</div>
        <div class="flex h-5 items-center gap-2 whitespace-nowrap">
            <span class="flex -space-x-1">
                <span class="size-2 rounded-full bg-emerald-700"></span>
                <span class="size-2 rounded-full bg-emerald-700/40"></span>
                <span class="size-2 rounded-full bg-emerald-700/20"></span>
            </span>
            <span class="text-emerald-700">{{ $streakLabel }}</span>
        </div>
        <div class="flex h-5 items-center whitespace-nowrap">{{ $dateLabel }}</div>
    </div>
</div>
