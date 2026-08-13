<x-layouts.app title="Wins">
    <main class="container !py-12 !space-y-12">

        <x-wins.week-strip :week-days="$weekDays" :weekly-wins-count="$weeklyWinsCount" :timeline-view="$timelineView" />
        <x-wins.capture-form />

        <x-connection-invite-banner />

        <x-wins.timeline :recent-wins="$recentWins" :next-before="$nextRecentWinsBefore" :view="$timelineView" />

        <footer class="mt-24 border-t border-line/60 pt-12 text-center">
            <p class="mx-auto max-w-[40ch] text-pretty text-sm text-ink-soft">A new week begins soon. Consider what one small thing would make tomorrow feel like a victory.</p>
        </footer>
    </main>
</x-layouts.app>
