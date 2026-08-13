<x-layouts.app title="Connected agents — Wins">
    <main class="container" x-data="copyAgentSetup()">
        <div>
            <h1>Bring Wins into your conversations.</h1>
            <p class="mt-4 text-sm leading-6 text-stone-500">Codex, Claude, and other MCP-enabled agents can reflect with you while you are already chatting. They can only see or save your wins after you approve access.</p>
        </div>

        @if (session('status'))
            <div class="mb-6 rounded-2xl border border-stone-200 bg-white px-5 py-4 text-sm text-stone-900 shadow-sm">{{ session('status') }}</div>
        @endif

        <section class="card">
            <div class="max-w-xl">
                <div class="flex justify-between">
                    <h2>Set up your agent</h2>
                    <div class="mt-5 flex flex-wrap gap-x-5 gap-y-2">
                        <button type="button" class="text-sm font-medium text-emerald-700 transition-colors hover:text-emerald-800" @click="codexSetupOpen = true">Set up Codex</button>
                        <button type="button" class="text-sm font-medium text-emerald-700 transition-colors hover:text-emerald-800" @click="claudeSetupOpen = true">Set up Claude</button>
                    </div>
                </div>

                <p class="mt-2 text-sm leading-6 text-stone-500">Add the secure Wins MCP server in your agent’s connections or integrations settings. The agent will then open a browser sign-in so you can approve access to your own journal.</p>
            </div>

            <ol class="mt-6 space-y-5">
                <li class="flex gap-4">
                    <span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-stone-100 text-xs font-medium text-stone-900">1</span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-stone-900">Add this MCP server address</p>
                        <div class="mt-2 flex items-center gap-2 rounded-xl border border-stone-200 bg-stone-50 p-2">
                            <input x-ref="serverUrl" type="text" readonly aria-label="Wins MCP server address" value="{{ url('/mcp/wins') }}" class="min-w-0 flex-1 truncate bg-transparent px-2 text-xs text-stone-600 outline-none">
                            <button type="button" class="rounded-lg bg-white px-3 py-2 text-xs font-medium text-stone-900 shadow-sm ring-1 ring-stone-200 transition-colors hover:bg-stone-100" @click="copy($refs.serverUrl.value, 'server')" x-text="labels.server">Copy</button>
                        </div>
                    </div>
                </li>
                <li class="flex gap-4">
                    <span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-stone-100 text-xs font-medium text-stone-900">2</span>
                    <p class="pt-0.5 text-sm leading-6 text-stone-500">Connect, then sign in to Wins in the browser window that opens. Review and approve the requested access.</p>
                </li>
                <li class="flex gap-4">
                    <span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-stone-100 text-xs font-medium text-stone-900">3</span>
                    <p class="pt-0.5 text-sm leading-6 text-stone-500">Paste the reflection preference below into your agent. It helps the check-in stay calm and entirely optional.</p>
                </li>
            </ol>
        </section>

        <section class="card">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2>A prompt for your agent</h2>
                    <p class="mt-2 text-sm leading-6 text-stone-500">This does not schedule notifications. It lets an agent make space for reflection when you are already talking.</p>
                </div>
                <button type="button" class="button-small" @click="copy($refs.reflectionPrompt.value, 'prompt')" x-text="labels.prompt">Copy prompt</button>
            </div>

            <textarea x-ref="reflectionPrompt" readonly class="mt-5 min-h-64 w-full resize-y rounded-xl border border-stone-200 bg-stone-50 p-4 text-sm leading-6 text-stone-700 outline-none transition focus:border-emerald-700 focus:ring-2 focus:ring-emerald-100">Use my connected Wins MCP server to support a calm daily reflection.

When we are already talking later in my day, use get-today-status. Only when is_check_in_day is true and both remaining_goal_wins and remaining_wins are greater than zero, ask once: “Is there something from today you feel good about?”

Do not frame this as unfinished work, a goal, or a productivity score. Do not follow up if I decline. Only use add-win after I explicitly confirm the words I want saved.</textarea>
        </section>

        <section class="mt-6 rounded-2xl border border-stone-200 bg-stone-100 p-5 hidden">
            <h2 class="text-sm font-medium text-stone-900">You stay in control</h2>
            <p class="mt-2 text-sm leading-6 text-stone-500">
                @if(false)
                Wins does not send browser notifications from this connection.
                @endif
                Your agent can only check in while you are already using it, and you can disconnect any agent below at any time.
            </p>
        </section>

        <section class="mt-10">
            <div class="mb-4">
                <h2>Connected agents</h2>
            </div>

            <div class="space-y-4">
            @forelse ($connections as $connection)
                <article class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between card">
                    <div>
                        <h3 class="font-medium text-stone-900">{{ $connection['name'] }}</h3>
                        <p class="mt-1 text-sm text-stone-500">Connected {{ $connection['connected_at']->diffForHumans() }} · Can read your wins and save one when you ask</p>
                    </div>
                    <form method="POST" action="{{ route('connections.destroy', $connection['client_id']) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-xl border border-stone-200 px-4 py-2 text-sm font-medium text-stone-900 transition-colors hover:border-stone-300 hover:bg-stone-50">Disconnect</button>
                    </form>
                </article>
            @empty
                <div class="rounded-2xl border border-stone-200 bg-white p-8 text-center shadow-sm">
                    <p class="text-2xl text-stone-900">Nothing is connected yet.</p>
                    <p class="mt-2 text-sm text-stone-500">When you authorize an agent, it will appear here.</p>
                </div>
            @endforelse
            </div>
        </section>

        <div
            x-cloak
            x-show="codexSetupOpen"
            x-on:keydown.escape.window="codexSetupOpen = false"
            class="fixed inset-0 z-50 flex items-end bg-stone-900/30 p-4 sm:items-center sm:justify-center"
            role="dialog"
            aria-modal="true"
            aria-labelledby="codex-setup-title"
        >
            <div class="w-full max-w-xl rounded-2xl border border-stone-200 bg-white p-6 shadow-xl" @click.outside="codexSetupOpen = false">
                <div class="flex items-start justify-between gap-6">
                    <div>
                        <p class="text-sm font-medium text-emerald-700">Codex CLI</p>
                        <h2 id="codex-setup-title" class="mt-2">Connect Wins to Codex</h2>
                    </div>
                    <button type="button" class="rounded-lg p-2 text-stone-500 transition-colors hover:bg-stone-100 hover:text-stone-900" @click="codexSetupOpen = false" aria-label="Close Codex setup">Close</button>
                </div>

                <ol class="mt-6 space-y-6">
                    <li>
                        <p class="text-sm font-medium text-stone-900">1. Add Wins, then sign in</p>
                        <p class="mt-1 text-sm leading-6 text-stone-500">Run these commands in your terminal. The second command opens the browser approval flow.</p>
                        <textarea x-ref="codexCommands" readonly aria-label="Codex setup commands" class="mt-3 min-h-24 w-full resize-y rounded-xl border border-stone-200 bg-stone-50 p-4 font-mono text-xs leading-6 text-stone-700 outline-none transition focus:border-emerald-700 focus:ring-2 focus:ring-emerald-100">codex mcp add wins --url {{ url('/mcp/wins') }}
codex mcp login wins</textarea>
                        <button type="button" class="button-small mt-3" @click="copy($refs.codexCommands.value, 'codex')" x-text="labels.codex">Copy commands</button>
                    </li>
                    <li>
                        <p class="text-sm font-medium text-stone-900">2. Approve access in your browser</p>
                        <p class="mt-1 text-sm leading-6 text-stone-500">Sign in to Wins and approve the connection. You can revoke it from this page at any time.</p>
                    </li>
                    <li>
                        <p class="text-sm font-medium text-stone-900">3. Start a new Codex task</p>
                        <p class="mt-1 text-sm leading-6 text-stone-500">Paste this reflection preference so Codex knows when and how to check in.</p>
                        <button type="button" class="button-small mt-3" @click="copy($refs.reflectionPrompt.value, 'prompt')" x-text="labels.prompt">Copy reflection prompt</button>
                    </li>
                </ol>
            </div>
        </div>

        <div
            x-cloak
            x-show="claudeSetupOpen"
            x-on:keydown.escape.window="claudeSetupOpen = false"
            class="fixed inset-0 z-50 flex items-end bg-stone-900/30 p-4 sm:items-center sm:justify-center"
            role="dialog"
            aria-modal="true"
            aria-labelledby="claude-setup-title"
        >
            <div class="w-full max-w-xl rounded-2xl border border-stone-200 bg-white p-6 shadow-xl" @click.outside="claudeSetupOpen = false">
                <div class="flex items-start justify-between gap-6">
                    <div>
                        <p class="text-sm font-medium text-emerald-700">Claude Code</p>
                        <h2 id="claude-setup-title" class="mt-2">Connect Wins to Claude</h2>
                    </div>
                    <button type="button" class="rounded-lg p-2 text-stone-500 transition-colors hover:bg-stone-100 hover:text-stone-900" @click="claudeSetupOpen = false" aria-label="Close Claude setup">Close</button>
                </div>

                <ol class="mt-6 space-y-6">
                    <li>
                        <p class="text-sm font-medium text-stone-900">1. Add Wins for your Claude Code account</p>
                        <p class="mt-1 text-sm leading-6 text-stone-500">Run this in your terminal. It makes Wins available in all of your Claude Code projects.</p>
                        <textarea x-ref="claudeCommand" readonly aria-label="Claude setup command" class="mt-3 min-h-20 w-full resize-y rounded-xl border border-stone-200 bg-stone-50 p-4 font-mono text-xs leading-6 text-stone-700 outline-none transition focus:border-emerald-700 focus:ring-2 focus:ring-emerald-100">claude mcp add --transport http --scope user wins {{ url('/mcp/wins') }}</textarea>
                        <button type="button" class="button-small mt-3" @click="copy($refs.claudeCommand.value, 'claude')" x-text="labels.claude">Copy command</button>
                    </li>
                    <li>
                        <p class="text-sm font-medium text-stone-900">2. Start Claude Code and approve access</p>
                        <p class="mt-1 text-sm leading-6 text-stone-500">Run <code class="rounded bg-stone-100 px-1.5 py-0.5 text-xs text-stone-700">claude</code>, enter <code class="rounded bg-stone-100 px-1.5 py-0.5 text-xs text-stone-700">/mcp</code>, then choose Wins to complete the browser sign-in and approval flow.</p>
                    </li>
                    <li>
                        <p class="text-sm font-medium text-stone-900">3. Start a new Claude conversation</p>
                        <p class="mt-1 text-sm leading-6 text-stone-500">Paste this reflection preference so Claude knows when and how to check in.</p>
                        <button type="button" class="button-small mt-3" @click="copy($refs.reflectionPrompt.value, 'prompt')" x-text="labels.prompt">Copy reflection prompt</button>
                    </li>
                </ol>
            </div>
        </div>
    </main>
</x-layouts.app>
