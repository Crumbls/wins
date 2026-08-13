@props(['title' => 'Wins'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="A calm daily ritual for noticing what went well.">
        <title>{{ $title }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-stone-50 font-sans text-stone-900 antialiased selection:bg-emerald-100 selection:text-emerald-900">
        <nav class="sticky top-0 z-10 border-b border-stone-200 bg-stone-50/90 backdrop-blur-md">
            <div class="container !py-0">
                <div class="flex items-center justify-between gap-4 !py-4">
                <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="text-sm font-medium tracking-tight text-stone-900">Wins</a>

                @auth
                    <div class="flex items-center gap-3">
                        <x-status-ticker />
                        <a href="{{ route('connections.index') }}" class="text-xs font-medium text-ink-soft transition-colors hover:text-ink">Connections</a>

                        <form method="POST" action="{{ route('logout') }}" class="flex items-center">
                            @csrf
                            <button type="submit" class="text-xs font-medium text-ink-soft transition-colors hover:text-ink">Log out</button>
                        </form>
                    </div>
                    <div class="hidden">
                        <form method="POST" action="{{ route('timezone.update') }}" data-timezone-form>
                            @csrf
                            <input type="hidden" name="timezone" value="{{ auth()->user()->timezone }}">
                        </form>
                    </div>
                @else
                    <div class="flex items-center gap-4 text-xs font-medium">
                        <a href="{{ route('login') }}" class="text-stone-500 transition-colors hover:text-stone-900">Log in</a>
                        <a href="{{ route('register') }}" class="button-small">Create account</a>
                    </div>
                @endauth
                </div>
            </div>
        </nav>

        {{ $slot }}

        <x-layouts.colophon />

        @auth
            <x-layouts.back-to-top />
        @endauth

    </body>
</html>
