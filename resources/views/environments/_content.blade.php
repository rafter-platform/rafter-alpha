@include('environments._header')

<div class="flex">
    <nav class="w-40 flex-shrink-0 text-gray-700 leading-loose">
        <ul>
            <li><a href="{{ route('projects.environments.show', [$project, $environment]) }}">Deployments</a></li>
            <li><a href="{{ route('projects.environments.database.index', [$project, $environment]) }}">Databases</a></li>
            <li><a href="{{ route('projects.environments.settings.index', [$project, $environment]) }}">Settings</a></li>
        </ul>
    </nav>

    <div class="ml-4">
        @if ($title ?? false)
            @include('components.subtitle', ['title' => $title])
        @endif

        @include('components.flash')

        {{ $slot }}
    </div>
</div>
