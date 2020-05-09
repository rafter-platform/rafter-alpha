<a {{ $attributes->merge([ 'class' => $classList ]) }} href="{{ $href }}">
    {{ $slot }}
</a>
