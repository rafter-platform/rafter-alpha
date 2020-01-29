<div class="flex flex-wrap mb-6">
    <label for="{{ $name }}" class="block text-gray-700 text-sm font-bold mb-2">
        {{ $label }}:
    </label>

    <textarea id="{{ $name }}" class="form-textarea w-full @error($name) border-red-500 @enderror" name="{{ $name }}" value="{{ old($name) }}" {{ $required ? 'required' : ''}}></textarea>

    @error($name)
        <p class="text-red-500 text-xs italic mt-4">
            {{ $message }}
        </p>
    @enderror
</div>
