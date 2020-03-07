<x-layout>
    <div class="mb-8 flex items-center justify-between border-b border-b-2 pb-4">
        <x-title class="mb-0">
            <h1>{{ $project->name }}</h1>
        </x-title>

        <span class="text-sm uppercase text-gray-600">
            {{ $project->googleProject->name }} - {{ $project->region }}
        </span>
    </div>

    <x-flash />
    <x-subtitle>Environments</x-subtitle>

    <p class="mb-4 text-gray-600">
        By default, production and staging environments are automatically created for your project. You can add additional environments and
        configure them to auto-deploy when you push commits to certain branches.
    </p>

    @foreach ($project->environments as $environment)
        <x-item
            :link="route('projects.environments.show', [$project, $environment])"
        >
            <x-slot name="title">{{ $environment->name}}</x-slot>
            <x-slot name="meta">{{ $environment->url ?? 'URL not yet available' }}</x-slot>
            <x-slot name="status">
                Last deployed {{ $environment->deployments()->latest()->first()->created_at->diffForHumans() }}
            </x-slot>
        </x-item>
    @endforeach
</x-layout>
