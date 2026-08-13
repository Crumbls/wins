<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Authorize {{ $client->name }} — Wins</title>
        @vite(['resources/css/app.css'])
    </head>
    <body class="min-h-screen bg-stone-50 font-sans text-stone-900 antialiased">
        <main class="mx-auto flex min-h-screen max-w-xl items-center px-6 py-12">
            <section class="w-full rounded-3xl border border-stone-200 bg-white p-8 shadow-sm sm:p-10">
                <p class="text-sm font-medium text-brand">Connect an agent</p>
                <h1 class="mt-2 font-serif text-4xl italic text-ink">Authorize {{ $client->name }}?</h1>
                <p class="mt-4 text-sm leading-6 text-ink-soft">This agent will be able to read your Wins journal and add a win when you explicitly ask it to. It cannot access anyone else’s journal.</p>
                <div class="mt-8 rounded-2xl bg-stone-50 p-5">
                    <p class="text-xs font-medium uppercase tracking-widest text-stone-500">Signed in as</p>
                    <p class="mt-2 font-medium text-ink">{{ $user->email }}</p>
                </div>
                <p class="mt-5 text-sm leading-6 text-ink-soft">You can disconnect this agent any time from your connected agents page.</p>
                <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row">
                    <form method="POST" action="{{ route('passport.authorizations.deny') }}" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="client_id" value="{{ $client->id }}">
                        <input type="hidden" name="auth_token" value="{{ $authToken }}">
                        <button type="submit" class="w-full rounded-xl border border-stone-200 px-5 py-3 text-sm font-medium text-ink transition-colors hover:bg-stone-50">Cancel</button>
                    </form>
                    <form method="POST" action="{{ route('passport.authorizations.approve') }}" class="flex-1">
                        @csrf
                        <input type="hidden" name="client_id" value="{{ $client->id }}">
                        <input type="hidden" name="auth_token" value="{{ $authToken }}">
                        <button type="submit" class="w-full rounded-xl bg-emerald-700 px-5 py-3 text-sm font-medium text-white shadow-sm transition-colors hover:bg-emerald-800">Authorize agent</button>
                    </form>
                </div>
            </section>
        </main>
    </body>
</html>
