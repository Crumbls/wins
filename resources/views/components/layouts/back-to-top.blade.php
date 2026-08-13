<div>
    <!-- Because you are alive, everything is possible. - Thich Nhat Hanh -->
</div>
<button type="button"
        class="fixed right-5 bottom-5 z-20 inline-flex size-11 items-center justify-center rounded-full border border-stone-200 bg-white text-stone-900 shadow-lg shadow-stone-900/10 transition hover:bg-stone-100 focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:ring-offset-2 motion-reduce:transition-none sm:right-8 sm:bottom-8"
        x-data="backToTop()"
        x-init="updateVisibility()"
        x-cloak
        x-show="visible"
        x-on:click="scrollToTop"
        x-on:scroll.window.throttle.100ms="updateVisibility"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="scale-90 opacity-0"
        x-transition:enter-end="scale-100 opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="scale-100 opacity-100"
        x-transition:leave-end="scale-90 opacity-0"
        aria-label="Back to top">
    <svg class="size-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
        <path d="m5 11 5-5 5 5M10 6v9" stroke-linecap="round" stroke-linejoin="round" />
    </svg>
</button>
