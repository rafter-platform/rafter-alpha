<a href="{{ $href }}" class="px-4 py-2 text-sm leading-5 text-gray-700 hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus:bg-gray-100 focus:text-gray-900 {{ $icon ?? false ? 'group flex items-center' : 'block' }}">
    {{ $icon ?? '' }}
    {{ $slot }}
</a>
