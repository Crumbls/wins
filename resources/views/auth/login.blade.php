<x-layouts.app title="Log in — Wins">
    <main class="mx-auto flex min-h-[calc(100vh-65px)] max-w-6xl items-center px-6 py-12 sm:py-16">
        <div class="grid w-full overflow-hidden rounded-3xl bg-surface shadow-sm ring-1 ring-black/5 lg:grid-cols-[1.15fr_0.85fr]">
            <section class="relative overflow-hidden bg-muted/70 p-8 sm:p-12 lg:p-14">
                <div class="pointer-events-none absolute -top-24 -right-20 size-64 rounded-full bg-brand/10 blur-3xl" aria-hidden="true"></div>
                <div class="pointer-events-none absolute -bottom-24 -left-16 size-56 rounded-full bg-brand/5 blur-3xl" aria-hidden="true"></div>

                <div class="relative">
                    <p class="text-sm font-medium text-brand">A calmer daily ritual</p>
                    <h1 class="mt-3 font-serif text-4xl leading-[0.95] italic text-ink sm:text-5xl">Notice the proof that you’re moving forward.</h1>

                    <div class="mt-7 max-w-xl space-y-4 text-base leading-7 text-ink-soft">
                        <p>Wins is a simple way to notice and track what you’re doing well throughout the day.</p>
                        <p>Instead of starting with another overwhelming to-do list, you record the progress you’ve already made—the problems you solved, the things you finished, and the moments you’re proud of.</p>
                        <p>Over time, those small wins reveal your momentum, reinforce the habits that are working, and help you build consistency around progress rather than pressure.</p>
                    </div>
                </div>
            </section>

            <section class="flex items-center p-8 sm:p-12 lg:p-14">
                <div class="mx-auto w-full max-w-sm">
                    <div class="mb-8">
                        <p class="text-sm font-medium text-brand">Welcome back</p>
                        <h2 class="mt-2 font-serif text-4xl italic text-ink md:hidden">Notice what went well.</h2>
                        <p class="mt-3 text-sm leading-6 text-ink-soft">Your wins are waiting for you.</p>
                    </div>

                    <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
                        @csrf
                        <x-form.input name="email" label="Email address" type="email" autocomplete="email" required autofocus />
                        <x-form.input name="password" label="Password" type="password" autocomplete="current-password" required />

                        <label class="flex items-center gap-2 text-sm text-ink-soft">
                            <input type="checkbox" name="remember" value="1" class="rounded border-line text-brand focus:ring-brand">
                            Keep me signed in
                        </label>

                        <button type="submit" class="w-full rounded-xl bg-emerald-700 px-5 py-3 text-sm font-medium text-white shadow-sm transition-colors hover:bg-emerald-800">Log in</button>
                    </form>

                    <p class="mt-6 text-center text-sm text-ink-soft">New here? <a href="{{ route('register') }}" class="font-medium text-brand hover:underline">Create an account</a>.</p>
                </div>
            </section>
        </div>
    </main>
</x-layouts.app>
