@php
    switch (strtolower($status)) {
        case 'pending':
            $color = 'yellow';
            break;

        case 'ready':
        case 'done':
            $color = 'green';
            break;

        default:
            $color = 'gray';
            break;
    }
@endphp

<div class="uppercase tracking-wide text-xs text-{{ $color }}-600 bg-{{ $color }}-200 rounded p-1 px-2">
    {{ $status }}
</div>
