@if ($attributes['href'] ?? false)
<a {{ $attributes->merge(['class' => $classList]) }}>
    {{ $slot }}
</a>
@else
<button {{ $attributes->merge(['class' => $classList, 'type' => 'submit']) }}">
    {{ $slot }}
</button>
@endif
