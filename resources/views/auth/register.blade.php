<x-layouts.app title="Create an account — Wins">
    <main class="mx-auto flex min-h-[calc(100vh-65px)] max-w-6xl items-center px-6 py-12 sm:py-16">
        <div class="grid w-full overflow-hidden rounded-3xl bg-surface shadow-sm ring-1 ring-black/5 lg:grid-cols-[1.15fr_0.85fr]">
            <section class="relative overflow-hidden bg-muted/70 p-8 sm:p-12 lg:p-14">
                <div class="pointer-events-none absolute -top-24 -right-20 size-64 rounded-full bg-brand/10 blur-3xl" aria-hidden="true"></div>
                <div class="pointer-events-none absolute -bottom-24 -left-16 size-56 rounded-full bg-brand/5 blur-3xl" aria-hidden="true"></div>

                <div class="relative">
                    <p class="text-sm font-medium text-brand">A gentle place to begin</p>
                    <h1 class="mt-3 font-serif text-4xl leading-[0.95] italic text-ink sm:text-5xl">Make room for what went well.</h1>

                    <div class="mt-7 max-w-xl space-y-4 text-base leading-7 text-ink-soft">
                        <p>Wins is a private place to keep track of the progress that can otherwise disappear into a busy day.</p>
                        <p>Start with the things you solved, finished, learned, or simply felt proud of. The small moments count.</p>
                        <p>Over time, your wins become a calm record of the momentum you are already building.</p>
                    </div>
                </div>
            </section>

            <section class="flex items-center p-8 sm:p-12 lg:p-14">
                <div class="mx-auto w-full max-w-sm">
                    <div class="mb-8">
                        <p class="text-sm font-medium text-brand">Create your account</p>
                        <h2 class="mt-2 font-serif text-4xl italic text-ink md:hidden">Start collecting wins.</h2>
                        <p class="mt-3 text-sm leading-6 text-ink-soft">A few honest moments can change how you see your week.</p>
                    </div>

                    <form method="POST" action="{{ route('register.store') }}" class="space-y-5" data-registration-form>
                        @csrf
                        <x-form.input name="name" label="Name" autocomplete="name" required autofocus />
                        <x-form.input name="email" label="Email address" type="email" autocomplete="email" required />
                        <x-form.input name="timezone" label="Your time zone" :value="old('timezone', 'UTC')" placeholder="America/Denver" autocomplete="off" data-timezone-field required />
                        <p class="-mt-2 text-xs leading-5 text-ink-faint">Detected from your device when possible. You can change it if it is not right.</p>
                        <x-form.input name="password" label="Password" type="password" autocomplete="new-password" required />
                        <x-form.input name="password_confirmation" label="Confirm password" type="password" autocomplete="new-password" required />

                        <button type="submit" class="w-full rounded-xl bg-emerald-700 px-5 py-3 text-sm font-medium text-white shadow-sm transition-colors hover:bg-emerald-800">Create account</button>
                    </form>

                    <p class="mt-6 text-center text-sm text-ink-soft">Already have an account? <a href="{{ route('login') }}" class="font-medium text-brand hover:underline">Log in</a>.</p>
                </div>
            </section>
        </div>
    </main>
</x-layouts.app>
