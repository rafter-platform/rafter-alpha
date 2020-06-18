<div class="flex flex-wrap mb-6" {{ $attributes->only('x-show') }}>
    <x-label for="{{ $name }}">
        {{ $label }}:
    </x-label>

    <select
        id="{{ $name }}"
        class="form-select w-full @error($name) border-red-500 @enderror"
        value="{{ old($name) }}"
        @error($name) aria-invalid="true" aria-describedby="{{ $name }}-error" @enderror
        {{ $attributes->except('options', 'x-show') }}>
        @foreach ($options as $value => $label)
            <option value="{{ $value }}">{{ $label }}</option>
        @endforeach
    </select>

    @error($name)
        <x-validation-error id="{{ $name }}-error">
            {{ $message }}
        </x-validation-error>
    @enderror
</div>
