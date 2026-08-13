@props(['weekDays', 'weeklyWinsCount', 'timelineView'])

@php
    $initialCounts = $weekDays->map(fn (array $day): int => $day['wins']->count())->values();
    $weekDates = $weekDays->map(fn (array $day): string => $day['date']->toDateString())->values();
@endphp

<section
    class="card"
    x-data="weeklyWins({{ \Illuminate\Support\Js::from($initialCounts) }}, {{ \Illuminate\Support\Js::from($weekDates) }}, {{ \Illuminate\Support\Js::from($timelineView) }})"
    x-on:weekly-wins:add.window="add($event.detail)"
    x-on:weekly-wins:remove.window="remove($event.detail)"
    x-on:weekly-wins:set-total.window="setTotal($event.detail)"
    x-on:wins-timeline:view-changed.window="timelineView = $event.detail.view"
    x-show="timelineView !== 'weekly'"
>
    <div class="mb-6 flex items-end justify-between">
        <h2 class="text-sm font-medium text-stone-500">This week</h2>
        <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-medium text-stone-500"><span x-text="total">{{ $weeklyWinsCount }}</span> Win</span>
    </div>

    <div class="grid grid-cols-7 gap-3">
        @foreach ($weekDays as $index => $day)
            @php($isToday = $day['date']->isToday())
            <div class="flex flex-col items-center gap-2">
                <div
                    x-cloak
                    x-show="!hasWins({{ $index }})"
                    @class(['aspect-[3/4] w-full rounded-lg border border-dashed border-stone-200', 'border-solid bg-emerald-50 ring-1 ring-emerald-700/20' => $isToday])
                ></div>
                <div
                    x-cloak
                    x-show="hasWins({{ $index }})"
                    x-bind:aria-label="`${counts[{{ $index }}]} ${counts[{{ $index }}] === 1 ? 'win' : 'wins'}`"
                    @class(['flex aspect-[3/4] w-full flex-col-reverse gap-1 overflow-hidden rounded-lg p-1 ring-1', 'bg-stone-100 ring-stone-200' => ! $isToday, 'bg-emerald-50 ring-emerald-700/20' => $isToday])
                >
                    <template x-for="bar in displayBars({{ $index }})" :key="bar">
                        <div class="h-2 w-full shrink-0 rounded-sm bg-emerald-700/65 transition-all"></div>
                    </template>
                </div>

                <span @class(['text-[10px]', 'font-bold text-emerald-700' => $isToday, 'font-medium text-stone-400' => ! $isToday])>{{ $day['date']->format('D') }}</span>
            </div>
        @endforeach
    </div>
</section>
