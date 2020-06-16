@php
$classes = "bg-white border-2 rounded-lg flex items-center cursor-pointer hover:bg-gray-200 mt-2 w-full ";
$classes .= ($small ?? false) ? 'px-2 py-2 text-sm' : 'px-3 py-4';
@endphp

@if (!empty($name))
<div>
    <input type="radio" class="sr-only" id="{{ $name }}_{{ $value }}" {{ $attributes }}>
    <label for="{{ $name }}_{{ $value }}" class="{{ $classes }}">
        {{ $icon ?? '' }}
        <div class="{{ empty($icon) ? '' : 'ml-2' }}">{{ $slot }}</div>
    </label>
</div>
@else
<div>
    <button {{ $attributes }} class="{{ $classes }}">
        {{ $icon ?? '' }}
        <div class="{{ empty($icon) ? '' : 'ml-2' }}">{{ $slot }}</div>
    </label>
</div>
@endif
