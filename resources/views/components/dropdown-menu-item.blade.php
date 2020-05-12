@if ($href)
<a href="{{ $href }}" {{ $attributes->merge([ 'class' => $classList() ])}}>
    {{ $icon ?? '' }}
    {{ $slot }}
</a>
@else
<button {{ $attributes->merge([ 'class' => $classList() ])}}>
    {{ $icon ?? '' }}
    {{ $slot }}
</button>
@endif
