<div class="flex items-center">
    <input id="{{ $name }}_{{ $value }}" type="radio" class="form-radio h-4 w-4 text-blue-600 transition duration-150 ease-in-out" {{ $attributes }} />
    <label for="{{ $name }}_{{ $value }}" class="ml-3">
        <span class="block text-sm leading-5 font-medium text-gray-700">{{ $slot }}</span>
    </label>
</div>
