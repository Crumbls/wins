<x-layouts.app title="Connected agents — Wins">
    <main class="mx-auto max-w-2xl px-6 py-12">
        <div class="mb-10 max-w-xl">
            <p class="text-sm font-medium text-brand">Privacy and control</p>
            <h1 class="mt-2 font-serif text-4xl italic text-ink">Connected agents</h1>
            <p class="mt-4 text-sm leading-6 text-ink-soft">Agents can only see and add your wins after you approve a connection. You can disconnect one at any time.</p>
        </div>

        @if (session('status'))
            <div class="mb-6 rounded-2xl border border-stone-200 bg-white px-5 py-4 text-sm text-ink shadow-sm">{{ session('status') }}</div>
        @endif

        <div class="space-y-4">
            @forelse ($connections as $connection)
                <article class="flex flex-col gap-5 rounded-2xl border border-stone-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="font-medium text-ink">{{ $connection['name'] }}</h2>
                        <p class="mt-1 text-sm text-ink-soft">Connected {{ $connection['connected_at']->diffForHumans() }} · Can read and add your wins</p>
                    </div>
                    <form method="POST" action="{{ route('connections.destroy', $connection['client_id']) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-xl border border-stone-200 px-4 py-2 text-sm font-medium text-ink transition-colors hover:border-stone-300 hover:bg-stone-50">Disconnect</button>
                    </form>
                </article>
            @empty
                <div class="rounded-2xl border border-stone-200 bg-white p-8 text-center shadow-sm">
                    <p class="font-serif text-2xl italic text-ink">Nothing is connected yet.</p>
                    <p class="mt-2 text-sm text-ink-soft">When you authorize an agent, it will appear here.</p>
                </div>
            @endforelse
        </div>
    </main>
</x-layouts.app>
