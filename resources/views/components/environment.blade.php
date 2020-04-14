<x-environment-header :project="$project" :environment="$environment" />

<div class="flex">
    <nav class="w-40 flex-shrink-0 text-gray-700 leading-loose">
        <ul>
            <li><a href="{{ route('projects.environments.show', [$project, $environment]) }}">Deployments</a></li>
            <li><a href="{{ route('projects.environments.logs', [$project, $environment]) }}">Logs</a></li>
            <li><a href="{{ route('projects.environments.database.index', [$project, $environment]) }}">Databases</a></li>
            <li><a href="{{ route('projects.environments.settings.index', [$project, $environment]) }}">Settings</a></li>
        </ul>
    </nav>

    <div class="ml-4">
        @if ($title ?? false)
            <x-subtitle>{{ $title }}</x-subtitle>
        @endif

        <x-flash></x-flash>

        {{ $slot }}
    </div>
</div>
