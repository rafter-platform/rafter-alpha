<fieldset class="mb-8">
    @if ($label ?? false)
        <legend class="block text-gray-700 text-sm font-medium">{{ $label }}</legend>
    @endif

    @if ($helper ?? false)
        <x-helper-text>{{ $helper }}</x-helper-text>
    @endif

    <div class="mt-4 space-y-4">
        {{ $slot }}
    </div>

    @if ($name ?? false)
        @error($name)
            <x-validation-error id="{{ $name }}-error">
                {{ $message }}
            </x-validation-error>
        @enderror
    @endif
</fieldset>
