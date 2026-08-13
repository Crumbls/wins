@props(['recentWins', 'nextBefore'])

<section class="space-y-6" x-data="winTimeline({{ \Illuminate\Support\Js::from(route('wins.feed')) }}, {{ \Illuminate\Support\Js::from($nextBefore) }})">
    <div class="flex items-center justify-between border-b border-stone-200 pb-4">
        <h2 class="text-sm font-medium uppercase tracking-widest text-stone-500">Recent wins</h2>
        <span class="text-xs font-medium text-stone-500">Worth remembering</span>
    </div>

    @if ($recentWins->isEmpty())
        <div class="rounded-2xl border border-stone-200 bg-white p-8 text-center shadow-sm">
            <p class="font-serif text-2xl italic text-ink">Your first win belongs here.</p>
            <p class="mt-2 text-sm text-ink-soft">Make room for the small things worth remembering.</p>
        </div>
    @else


        <div class="space-y-5">
            @foreach ($recentWins as $date => $wins)
                <div class="group rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center gap-3">
                        <span class="text-sm font-semibold text-ink">{{ \Illuminate\Support\Carbon::parse($date)->format('l, M j') }}</span>
                        <div class="h-px flex-1 bg-line/70 transition-colors group-hover:bg-line"></div>
                    </div>
                    <ul class="space-y-4">
                        @foreach ($wins as $win)
                            <li class="max-w-[56ch] text-pretty text-ink-soft">{{ $win->content }}</li>
                        @endforeach
                    </ul>
                </div>
            @endforeach

            <template x-for="group in groups" :key="group.date">
                <div class="group rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center gap-3">
                        <span class="text-sm font-semibold text-ink" x-text="formatDate(group.date)"></span>
                        <div class="h-px flex-1 bg-line/70 transition-colors group-hover:bg-line"></div>
                    </div>
                    <ul class="space-y-4">
                        <template x-for="win in group.wins" :key="win.id">
                            <li class="max-w-[56ch] text-pretty text-ink-soft" x-text="win.content"></li>
                        </template>
                    </ul>
                </div>
            </template>

            <div class="h-6" x-show="hasMore && !failed" x-intersect.margin.200px="loadMore" aria-hidden="true"></div>
            <p class="text-center text-sm text-ink-faint" x-cloak x-show="loading" role="status">Loading earlier wins...</p>
            <p class="text-center text-sm text-red-600" x-cloak x-show="error" x-text="error" role="alert"></p>
        </div>
    @endif
</section>
