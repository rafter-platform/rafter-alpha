<span class="inline-flex rounded-md shadow-sm">
    @if ($attributes['href'] ?? false)
    <a {{ $attributes }} class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm leading-5 font-medium rounded-md text-gray-700 bg-white hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:text-gray-800 active:bg-gray-50 transition ease-in-out duration-150">
        {{ $slot }}
    </a>
    @else
    <button {{ $attributes->merge(['type' => 'submit']) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm leading-5 font-medium rounded-md text-gray-700 bg-white hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:text-gray-800 active:bg-gray-50 transition ease-in-out duration-150">
        {{ $slot }}
    </button>
    @endif
</span>
