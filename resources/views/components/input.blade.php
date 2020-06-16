<div class="flex flex-wrap mb-6">
    <x-label for="{{ $name }}">
        {{ $label }}:
    </x-label>

    @if ($helper ?? false)
    <div class="text-sm text-gray-600 w-full mb-2">{{ $helper }}</div>
    @endif

    <input
        id="{{ $name }}"
        type="{{ $type ?? 'text' }}"
        class="form-input w-full @error($name) border-red-500 @enderror"
        name="{{ $name }}"
        value="{{ old($name) ?? $value ?? '' }}"
        {{ $attributes->except(['name', 'type', 'value']) }}
    />

    @error($name)
        <p class="text-red-500 text-xs italic mt-4">
            {{ $message }}
        </p>
    @enderror
</div>
