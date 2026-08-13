@props([
    'label',
    'name',
    'type' => 'text',
    'value' => null,
])

<div class="space-y-2">
    <label for="{{ $name }}" class="block text-sm font-medium text-ink-soft">{{ $label }}</label>
    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        @if ($type !== 'password') value="{{ old($name, $value) }}" @endif
        {{ $attributes->class(['w-full rounded-xl border bg-white px-3.5 py-3 text-stone-900 outline-none transition-colors placeholder:text-stone-400 focus:border-emerald-700 focus:ring-2 focus:ring-emerald-700/10', 'border-red-500' => $errors->has($name), 'border-stone-200' => ! $errors->has($name)]) }}
    >
    @error($name)
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
