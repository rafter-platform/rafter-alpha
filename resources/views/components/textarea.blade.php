<div class="flex flex-wrap mb-6" {{ $attributes->only('x-show') }}>
    <x-label for="{{ $name }}">
        {{ $label }}:
    </x-label>

    @if ($helper ?? false)
        <x-helper-text>{{ $helper }}</x-helper-text>
    @endif

    <textarea
        id="{{ $name }}"
        class="form-textarea w-full @error($name) border-red-500 @enderror {{ $classes ?? '' }}"
        name="{{ $name }}"
        value="{{ $value ?? old($name) }}"
        @error($name) aria-invalid="true" aria-describedby="{{ $name }}-error" @enderror
        rows="10"
        {{ $attributes->except(['name', 'value', 'x-show']) }}
    ></textarea>

    @error($name)
        <x-validation-error id="{{ $name }}-error">
            {{ $message }}
        </x-validation-error>
    @enderror
</div>
