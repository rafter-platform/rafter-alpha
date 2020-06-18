<div class="flex flex-wrap mb-6">
    <x-label for="{{ $name }}">
        {{ $label }}:
    </x-label>

    <div class="text-sm text-gray-600 w-full mb-2">{{ $helper ?? '' }}</div>

    <textarea
        id="{{ $name }}"
        class="form-textarea w-full @error($name) border-red-500 @enderror {{ $classes ?? '' }}"
        name="{{ $name }}"
        value="{{ $value ?? old($name) }}"
        @error($name) aria-invalid="true" aria-describedby="{{ $name }}-error" @enderror
        rows="10"
        {{ ($required ?? false) ? 'required' : ''}}
        {{ ($disabled ?? false) ? 'disabled' : ''}}
    >{{ $value ?? '' }}</textarea>

    @error($name)
        <x-validation-error id="{{ $name }}-error">
            {{ $message }}
        </x-validation-error>
    @enderror
</div>
