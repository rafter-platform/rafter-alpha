@php
    $classes = "bg-blue-500 hover:bg-blue-700 text-gray-100 font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline inline-block";
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
