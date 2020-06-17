<div class="flex flex-wrap mb-6">
    <x-label for="{{ $name }}">
        {{ $label }}:
    </x-label>

    @if ($helper ?? false)
    <div class="text-sm text-gray-600 w-full mb-2">{{ $helper }}</div>
    @endif

    <div class="relative w-full">
        <input
            id="{{ $name }}"
            type="{{ $type ?? 'text' }}"
            class="form-input w-full @error($name) border-red-500 @enderror"
            @error($name) aria-invalid="true" aria-describedby="{{ $name }}-error" @enderror
            name="{{ $name }}"
            value="{{ old($name) ?? $value ?? '' }}"
            {{ $attributes->except(['name', 'type', 'value']) }}
        />
        @error($name)
        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
            <x-heroicon-s-exclamation-circle class="h-5 w-5 text-red-500" />
        </div>
        @enderror
    </div>

    @error($name)
        <p class="text-red-600 text-sm mt-2" id="{{ $name }}-error">
            {{ $message }}
        </p>
    @enderror
</div>
