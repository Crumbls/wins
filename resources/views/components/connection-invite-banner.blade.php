<section {{ $attributes->class('card p-5') }}>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2>Bring Wins into the conversations you already have.</h2>
            <p class="mt-2 max-w-xl text-sm leading-6 text-stone-500">Connect an agent to reflect with you while you are chatting. It will only save a win when you ask.</p>
        </div>

        <a href="{{ route('connections.index') }}" class="shrink-0 rounded-xl border border-stone-200 px-4 py-2 text-center text-sm font-medium text-stone-900 transition-colors hover:border-stone-300 hover:bg-stone-50">Explore connections</a>
    </div>
</section>
