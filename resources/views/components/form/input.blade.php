<div class="flex flex-wrap mb-6">
    <label for="{{ $name }}" class="block text-gray-700 text-sm font-bold mb-2">
        {{ $label }}:
    </label>

    <div class="text-sm text-gray-600 w-full mb-2">{{ $helper ?? '' }}</div>

    <input
        id="{{ $name }}"
        type="{{ $type ?? 'text' }}"
        class="form-input w-full @error($name) border-red-500 @enderror"
        name="{{ $name }}"
        value="{{ old($name) ?? $value ?? '' }}"
        {{ $required ? 'required' : ''}}
        {{ $min ?? false ? 'min=' . $min : '' }}
        {{ $max ?? false ? 'max=' . $max : '' }}
    />

    @error($name)
        <p class="text-red-500 text-xs italic mt-4">
            {{ $message }}
        </p>
    @enderror
</div>
