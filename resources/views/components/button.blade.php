@php
$classes = 'inline-flex items-center border font-medium rounded-md focus:outline-none transition ease-in-out duration-150 disabled:opacity-75';

switch ($design ?? '') {
    case 'primary':
        $classes .= ' ' . 'text-white border-transparent bg-blue-600 hover:bg-blue-500 focus:border-blue-700 focus:shadow-outline-blue active:bg-blue-700';
        break;

    default:
        $classes .= ' ' . 'border-gray-300 text-gray-700 bg-white hover:text-gray-500 focus:border-blue-300 focus:shadow-outline-blue active:text-gray-800 active:bg-gray-50';
        break;
}

switch ($size ?? '') {
    case 'xl':
        $classes .= ' ' . 'text-base leading-6 px-6 py-3';
        break;

    case 'lg':
        $classes .= ' ' . 'text-base leading-6 px-4 py-2';
        break;

    case 'sm':
        $classes .= ' ' . 'text-sm leading-4 px-3 py-2';
        break;

    case 'xs':
        $classes .= ' ' . 'text-xs leading-4 px-2.5 py-1.5';
        break;

    default:
        $classes .= ' ' . 'text-sm leading-5 px-4 py-2';
        break;
}
@endphp

<span class="inline-flex rounded-md shadow-sm">
    @if ($attributes['href'] ?? false)
    <a {{ $attributes }} class="{{ $classes }}">
        {{ $slot }}
    </a>
    @else
    <button {{ $attributes->merge(['type' => 'submit']) }}" class="{{ $classes }}">
        {{ $slot }}
    </button>
    @endif
</span>
