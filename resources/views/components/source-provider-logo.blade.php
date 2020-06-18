@if ($type == 'github')
    <x-icon-github class="{{ $class ?? '' }}" />
@elseif ($type == 'bitbucket')
    <x-icon-bitbucket class="{{ $class ?? '' }}" />
@elseif ($type == 'gitlab')
    <x-icon-gitlab class="{{ $class ?? '' }}" />
@else
    <x-heroicon-o-desktop-computer class="{{ $class ?? '' }}" />
@endif
