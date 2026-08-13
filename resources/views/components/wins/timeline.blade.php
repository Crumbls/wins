@props(['recentWins', 'nextBefore', 'view'])

@php
    $initialGroups = $recentWins->map(
        fn ($wins, $date): array => [
            'date' => $date,
            'wins' => $wins->map(
                fn ($win): array => [
                    'id' => $win->id,
                    'content' => $win->content,
                    'created_at' => $win->created_at->toIso8601String(),
                ],
            )->values(),
        ],
    )->values();
@endphp

<section class="space-y-6" x-data="winTimeline({{ \Illuminate\Support\Js::from(route('wins.feed')) }}, {{ \Illuminate\Support\Js::from($initialGroups) }}, {{ \Illuminate\Support\Js::from($nextBefore) }}, {{ \Illuminate\Support\Js::from($view) }})" x-on:weekly-wins:add.window="add($event.detail)">
    <form x-ref="viewPreferenceForm" action="{{ route('timeline-view.update') }}" class="hidden">
        @csrf
    </form>
    <div class="flex items-center justify-between border-b border-stone-200 pb-4">
        <h3 class="text-sm font-medium uppercase tracking-widest text-ink-faint">Recent wins</h3>
        <div class="flex rounded-lg border border-stone-200 bg-white p-1 text-xs font-medium" role="group" aria-label="Timeline grouping">
            <button type="button" class="rounded-md px-3 py-1.5 transition-colors" x-on:click="setView('daily')" x-bind:aria-pressed="view === 'daily'" x-bind:class="view === 'daily' ? 'bg-stone-100 text-stone-900 shadow-sm' : 'text-stone-500 hover:text-stone-900'">Daily</button>
            <button type="button" class="rounded-md px-3 py-1.5 transition-colors" x-on:click="setView('weekly')" x-bind:aria-pressed="view === 'weekly'" x-bind:class="view === 'weekly' ? 'bg-stone-100 text-stone-900 shadow-sm' : 'text-stone-500 hover:text-stone-900'">By week</button>
        </div>
    </div>

    <template x-if="groups.length === 0">
        <div class="card">
            <p class="font-serif text-2xl italic text-ink">Your first win belongs here.</p>
            <p class="mt-2 text-sm text-ink-soft">Make room for the small things worth remembering.</p>
        </div>
    </template>

    <div class="space-y-10" x-cloak x-show="groups.length > 0 && view === 'daily'">
            <template x-for="(group, index) in groups" :key="group.date">
                <div :class="index === 0 ? 'group' : 'group opacity-60 transition-opacity hover:opacity-100'">
                    <div class="mb-4 flex items-center gap-3">
                        <span class="date" x-text="formatDate(group.date)"></span>
                        <div class="h-px flex-1 bg-line/70 transition-colors group-hover:bg-line"></div>
                    </div>
                    <ul class="win-list">
                        <template x-for="win in group.wins" :key="win.id">
                            <li x-text="win.content"></li>
                        </template>
                    </ul>
                </div>
            </template>
    </div>

    <div class="space-y-10" x-cloak x-show="groups.length > 0 && view === 'weekly'">
        <template x-for="(week, index) in weeklyGroups" :key="week.start">
            <section :class="index === 0 ? 'group' : 'group opacity-60 transition-opacity hover:opacity-100'">
                <div class="mb-5 border-b border-stone-200 pb-5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-ink" x-text="formatWeekLabel(week.start)"></p>
                            <p class="mt-1 text-sm text-ink-soft" x-text="formatWeekRange(week.start)"></p>
                        </div>
                        <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-medium text-stone-500"><span x-text="week.total"></span> <span x-text="week.total === 1 ? 'win' : 'wins'"></span></span>
                    </div>

                    <div class="mt-5 grid grid-cols-7 gap-2">
                        <template x-for="day in week.days" :key="day.index">
                            <div class="flex flex-col items-center gap-1.5">
                                <div class="flex aspect-[3/4] w-full flex-col-reverse gap-0.5 overflow-hidden rounded-lg border p-0.5" x-bind:class="day.total > 0 ? 'border-stone-200 bg-stone-100' : 'border-dashed border-stone-200 bg-white'" x-bind:aria-label="`${day.total} ${day.total === 1 ? 'win' : 'wins'}`">
                                    <template x-for="bar in displayBars(day.total)" :key="bar">
                                        <span class="h-1.5 w-full shrink-0 rounded-sm bg-emerald-700/65"></span>
                                    </template>
                                </div>
                                <span class="text-[10px] font-medium text-stone-400" x-text="day.label"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="space-y-7">
                    <template x-for="group in week.groups" :key="group.date">
                        <div>
                            <div class="mb-4 flex items-center gap-3">
                                <span class="date" x-text="formatDate(group.date)"></span>
                                <div class="h-px flex-1 bg-line/70"></div>
                            </div>
                            <ul class="win-list">
                                <template x-for="win in group.wins" :key="win.id">
                                    <li x-text="win.content"></li>
                                </template>
                            </ul>
                        </div>
                    </template>
                </div>
            </section>
        </template>
    </div>

    <div class="h-6" x-show="hasMore && !failed" x-intersect.margin.200px="loadMore" aria-hidden="true"></div>
    <p class="text-center text-sm text-ink-faint" x-cloak x-show="loading" role="status">Loading earlier wins...</p>
    <p class="text-center text-sm text-red-600" x-cloak x-show="error" x-text="error" role="alert"></p>
</section>
