@php
    $classes = "button";
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
