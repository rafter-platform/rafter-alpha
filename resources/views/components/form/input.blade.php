<div class="flex flex-wrap mb-6">
    <label for="{{ $name }}" class="block text-gray-700 text-sm font-bold mb-2">
        {{ $label }}:
    </label>

    <input id="{{ $name }}" type="{{ $type ?? 'text' }}" class="form-input w-full @error($name) border-red-500 @enderror" name="{{ $name }}" value="{{ old($name) }}" {{ $required ? 'required' : ''}}>

    @error($name)
        <p class="text-red-500 text-xs italic mt-4">
            {{ $message }}
        </p>
    @enderror
</div>
