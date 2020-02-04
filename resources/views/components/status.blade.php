@php
    switch (strtolower($status)) {
        case 'pending':
        case 'in_progress':
        case 'creating':
        case 'started':
            $color = 'yellow';
            break;

        case 'ready':
        case 'done':
        case 'finished':
        case 'active':
        case 'successful':
            $color = 'green';
            break;

        case 'failed':
            $color = 'red';
            break;

        default:
            $color = 'gray';
            break;
    }
@endphp

<div class="uppercase tracking-wide text-xs text-{{ $color }}-600 bg-{{ $color }}-200 rounded p-1 px-2">
    {{ str_replace('_', ' ', $status) }}
</div>
