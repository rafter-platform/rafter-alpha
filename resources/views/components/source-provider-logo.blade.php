@if ($type == 'github')
    <x-icon-github class="{{ $class ?? '' }}" style="{{ $style ?? '' }}" />
@elseif ($type == 'bitbucket')
    <x-icon-bitbucket class="{{ $class ?? '' }}" style="{{ $style ?? '' }}" />
@elseif ($type == 'gitlab')
    <x-icon-gitlab class="{{ $class ?? '' }}" style="{{ $style ?? '' }}" />
@else
    <x-heroicon-o-desktop-computer class="{{ $class ?? '' }}" style="{{ $style ?? '' }}" />
@endif
