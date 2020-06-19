@if ($type == 'laravel')
    <x-icon-laravel class="{{ $class ?? '' }}" />
@elseif ($type == 'nodejs')
    <x-icon-nodejs class="{{ $class ?? '' }}" />
@elseif ($type == 'rails')
    <x-icon-rails class="{{ $class ?? '' }}" />
@else
    <x-heroicon-o-desktop-computer class="{{ $class ?? '' }}" />
@endif
