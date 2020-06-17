<x-slot name="title">{{ $project->name }}</x-slot>
<x-slot name="meta">
    <x-header-meta>
        <x:heroicon-o-sparkles class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" />
        {{ $project->typeLabel() }}
    </x-header-meta>
    <x-header-meta>
        <x:heroicon-o-folder class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" />
        {{ $project->googleProject->project_id }}
    </x-header-meta>
    <x-header-meta>
        <x:heroicon-o-location-marker class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" />
        {{ $project->region }}
    </x-header-meta>
    <x-header-meta>
        <x-github-icon class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" />
        {{ $project->repository }}
    </x-header-meta>
</x-slot>
<x-slot name="actions">
    <x-white-button :href="$environment->url">View</x-white-button>
</x-slot>

<div class="mb-4 sm:mb-0 sm:flex justify-between items-center ">
    <div class="md:flex items-center mb-4">
        <h2 class="text-lg font-bold mb-2 md:mb-0 mr-4">{{ ucfirst($environment->name) }}</h2>
        <div class="text-xs mr-4 mb-1 md:mb-0">
            <x:heroicon-o-globe class="mr-1.5 h-4 w-4 inline align-middle" />
            <a href="https://{{ $environment->primaryDomain() }}">{{ $environment->primaryDomain() }}</a>
            @if ($environment->additionalDomainsCount() > 0)
                <a title="{{ $environment->additionalDomainsCount() }} other domain(s)" href="{{ route('projects.environments.domains', [$project, $environment]) }}">(+{{ $environment->additionalDomainsCount() }})</a>
            @endif
        </div>
        <div class="text-xs mr-4">
            <x-github-icon class="mr-1.5 h-4 w-4 inline align-middle" />
            <span>
                Automatically deploys from <code>{{ $environment->branch }}</code>
            </span>
        </div>
    </div>
    <x-environment-switcher :project="$project" />
</div>

<x-environment-subnav :environment="$environment" />

@if ($title ?? false)
<x-subtitle>{{ $title }}</x-subtitle>
@endif

{{ $slot }}
