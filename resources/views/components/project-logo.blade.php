@if ($type == 'laravel')
    <x-icon-laravel class="{{ $class ?? '' }}" style="{{ $style ?? '' }}" />
@elseif ($type == 'nodejs')
    <x-icon-nodejs class="{{ $class ?? '' }}" style="{{ $style ?? '' }}" />
@elseif ($type == 'rails')
    <x-icon-rails class="{{ $class ?? '' }}" style="{{ $style ?? '' }}" />
@else
    <x-heroicon-o-desktop-computer class="{{ $class ?? '' }}" style="{{ $style ?? '' }}" />
@endif
