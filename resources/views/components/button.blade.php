@php
    $color = $color ?? 'blue';
    $classes = "bg-$color-500 text-gray-100 font-bold py-2 px-4 rounded inline-block hover:bg-$color-700 focus:outline-none focus:shadow-outline";
@endphp

@if ($link ?? false)
<a href="{{ $link }}" class="{{ $classes }}">
    {{ $slot }}
</a>
@else
<button type="submit" class="{{ $classes }}">
    {{ $slot }}
</button>
@endif
