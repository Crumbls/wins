<div>
    <!-- It is not the man who has too little, but the man who craves more, that is poor. - Seneca -->
</div>
<div class="fixed bottom-5 left-5 z-20 sm:bottom-8 sm:left-8" x-data="colophon()" x-on:keydown.escape.window="close">
    <div id="colophon-details"
         class="absolute bottom-full left-0 mb-3 w-72 rounded-2xl border border-line/80 bg-surface p-5 shadow-xl shadow-ink/10"
         x-cloak
         x-show="open"
         x-on:click.outside="close"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="translate-y-2 opacity-0"
         x-transition:enter-end="translate-y-0 opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="translate-y-0 opacity-100"
         x-transition:leave-end="translate-y-2 opacity-0"
         role="dialog"
         aria-labelledby="colophon-title">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p id="colophon-title" class="text-sm font-medium text-ink">Wins</p>
                <p class="mt-1 text-xs leading-relaxed text-ink-soft">A calm daily ritual by

                    <a href="https://crumbls.com" rel="external" target="_blank">
                        Crumbls, LLC.
                    </a>
                </p>
            </div>
            <button type="button" class="-m-1 inline-flex size-7 items-center justify-center rounded-full text-ink-soft transition hover:bg-muted hover:text-ink focus:outline-none focus:ring-2 focus:ring-brand" x-on:click="close" aria-label="Close site information">
                <svg class="size-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                    <path d="m6 6 8 8m0-8-8 8" stroke-linecap="round" />
                </svg>
            </button>
        </div>

        <nav class="mt-5 flex flex-wrap gap-x-4 gap-y-3 border-t border-line/60 pt-4 text-xs font-medium text-ink-soft" aria-label="Site information">
            <a href="{{ route('privacy') }}" class="transition hover:text-ink focus:outline-none focus:ring-2 focus:ring-brand">Privacy</a>
            <a href="{{ route('terms') }}" class="transition hover:text-ink focus:outline-none focus:ring-2 focus:ring-brand">Terms</a>
            <a href="https://www.crumbls.com/contact" class="transition hover:text-ink focus:outline-none focus:ring-2 focus:ring-brand">Contact</a>
            <a href="https://github.com/Crumbls/wins" class="transition hover:text-ink focus:outline-none focus:ring-2 focus:ring-brand" target="_blank">GitHub</a>
        </nav>
    </div>

    <button type="button"
            class="inline-flex items-center gap-1.5 rounded-full border border-line/80 bg-background/90 px-3 py-2 text-xs font-medium text-ink-soft shadow-sm shadow-ink/5 backdrop-blur transition hover:border-line hover:bg-surface hover:text-ink focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2 motion-reduce:transition-none"
            x-on:click.stop="open = !open"
            x-bind:aria-expanded="open"
            aria-controls="colophon-details">
        <span>Wins by Crumbls</span>
    </button>
</div>
