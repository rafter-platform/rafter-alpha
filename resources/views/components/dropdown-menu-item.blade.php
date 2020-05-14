@if ($href)
<a href="{{ $href }}" {{ $attributes->merge([ 'class' => $classList() ])}}>
    {{ $icon ?? '' }}
    {{ $slot }}
</a>
@else
<button @click="open = false" {{ $attributes->merge([ 'class' => $classList() ])}}>
    {{ $icon ?? '' }}
    {{ $slot }}
</button>
@endif
