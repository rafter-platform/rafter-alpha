<div class="flex flex-wrap mb-6">
    <x-label for="{{ $name }}">
        {{ $label }}
    </x-label>

    @if ($helper ?? false)
        <x-helper-text>{{ $helper }}</x-helper-text>
    @endif

    <input type="checkbox" id="{{ $name }}" {{ $attributes }} class="sr-only toggle" />

    <label for="{{ $name }}" class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:shadow-outline">
        <span aria-hidden="true" class="inline-block h-5 w-5 rounded-full bg-white shadow transform transition ease-in-out duration-200"></span>
    </label>

    @error($name)
        <x-validation-error id="{{ $name }}-error">
            {{ $message }}
        </x-validation-error>
    @enderror
</div>
