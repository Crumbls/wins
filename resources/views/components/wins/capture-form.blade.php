<section class="mb-20">
    <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
        <h1 class="mb-6 text-balance text-2xl font-medium leading-tight tracking-tight text-ink sm:text-3xl">What went well today?</h1>

        <form method="POST" action="{{ route('wins.store') }}" class="space-y-4" x-data="winCapture({{ \Illuminate\Support\Js::from(auth()->user()->timezone) }})" x-on:submit.prevent="submit">
            @csrf
            <div class="relative">
                <label class="sr-only" for="content">Today's win</label>
                <input id="content" name="content" type="text" value="{{ old('content') }}"
                       placeholder="I shipped the final assets for the project..."
                       maxlength="1000" class="w-full border-b border-stone-200 bg-transparent py-3 text-lg text-stone-900 transition-colors placeholder:text-stone-400 focus:border-emerald-700 focus:outline-none disabled:cursor-wait disabled:opacity-60" x-model="content" x-ref="content" x-bind:disabled="submitting">
                <p class="pt-2 text-sm text-red-600" x-cloak x-show="error" x-text="error" role="alert"></p>
                @error('content')
                    <p class="pt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end pt-4">
                <button type="submit" class="rounded-xl bg-emerald-700 px-5 py-3 text-sm font-medium text-white shadow-sm transition-colors hover:bg-emerald-800 disabled:cursor-not-allowed disabled:opacity-60"
                        x-bind:disabled="submitting || cooldown > 0"
                        x-bind:aria-busy="submitting" x-text="buttonLabel">Add a win</button>
            </div>
        </form>
    </div>
</section>
