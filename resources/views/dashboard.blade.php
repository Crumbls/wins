<x-layouts.app title="Wins" :status-date="$currentDate" :streak-days="$streakDays">
    <main class="mx-auto max-w-2xl px-6 py-12">

        <x-wins.week-strip :week-days="$weekDays" :weekly-wins-count="$weeklyWinsCount" />
        <x-wins.capture-form />
        <x-wins.timeline :recent-wins="$recentWins" :next-before="$nextRecentWinsBefore" />

        <footer class="mt-24 border-t border-line/60 pt-12 text-center">
            <p class="mx-auto max-w-[40ch] text-pretty text-sm text-ink-soft">A new week begins soon. Consider what one small thing would make tomorrow feel like a victory.</p>
        </footer>
    </main>
</x-layouts.app>
