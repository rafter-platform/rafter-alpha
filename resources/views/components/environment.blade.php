<x-slot name="title">{{ $project->name }}</x-slot>
<x-slot name="meta">
    <x-header-meta>
        <x:heroicon-o-sparkles class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" />
        {{ $project->typeLabel() }}
    </x-header-meta>
    <x-header-meta>
        <x:heroicon-o-folder class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" />
        {{ $project->googleProject->name }}
    </x-header-meta>
    <x-header-meta>
        <x:heroicon-o-location-marker class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" />
        {{ $project->region }}
    </x-header-meta>
</x-slot>
<x-slot name="actions">
    <x-white-button>View</x-white-button>
</x-slot>

<h2 class="text-lg font-bold mb-4">{{ ucfirst($environment->name) }}</h2>

<div class="mb-8">
    <div class="sm:hidden">
        <select aria-label="Selected tab" class="mt-1 form-select block w-full pl-3 pr-10 py-2 text-base leading-6 border-gray-300 focus:outline-none focus:shadow-outline-blue focus:border-blue-300 sm:text-sm sm:leading-5 transition ease-in-out duration-150">
            <option>My Account</option>
            <option>Company</option>
            <option selected>Team Members</option>
            <option>Billing</option>
        </select>
    </div>
    <div class="hidden sm:block">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex">
                <x-tab-item :href="route('projects.environments.show', [$project, $environment])">Overview</x-tab-item>
                <x-tab-item class="ml-8" :href="route('projects.environments.logs', [$project, $environment])">Logs</x-tab-item>
                <x-tab-item class="ml-8" :href="route('projects.environments.database.index', [$project, $environment])">Databases</x-tab-item>
                <x-tab-item class="ml-8" :href="route('projects.environments.settings.index', [$project, $environment])">Settings</x-tab-item>
            </nav>
        </div>
    </div>
</div>

@if ($title ?? false)
<x-subtitle>{{ $title }}</x-subtitle>
@endif

{{ $slot }}
