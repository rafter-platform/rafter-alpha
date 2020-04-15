<div class="flex flex-wrap mb-6">
    <label for="{{ $name }}" class="block text-gray-700 text-sm font-bold mb-2">
        {{ $label }}:
    </label>

    <select
        id="{{ $name }}"
        class="form-select w-full @error($name) border-red-500 @enderror"
        value="{{ old($name) }}"
        {{ $attributes->except('options') }}>
        @foreach ($options as $value => $label)
            <option value="{{ $value }}">{{ $label }}</option>
        @endforeach
    </select>

    @error($name)
        <p class="text-red-500 text-xs italic mt-4">
            {{ $message }}
        </p>
    @enderror
</div>
