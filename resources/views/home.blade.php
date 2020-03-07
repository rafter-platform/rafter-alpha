<x-layout>
    <x-title>
        <h1>{{ auth()->user()->currentTeam->name }} Dashboard</h1>
    </x-title>

    <x-flash />

    @if (auth()->user()->currentTeam->projects()->count())
        <x-subtitle>Projects</x-subtitle>

        @foreach (Auth::user()->currentTeam->projects as $project)
            <x-item :link="route('projects.show', $project)">
                <x-slot name="title">{{ $project->name }}</x-slot>
                <x-slot name="meta">{{ \App\Project::TYPES[$project->type] }} / {{ $project->region }} / {{ $project->googleProject->name }}</x-slot>
                <x-slot name="status">Last deployed {{ $project->environments()->first()->activeDeployment()->created_at->diffForHumans() }}</x-slot>
            </x-item>
        @endforeach
    @else
    <x-card class="md:w-1/2">
        <x-slot name="title"><h2>Rafter Onboarding</h2></x-slot>
        <p class="mb-6">To get started with Rafter, complete the following steps to deploy your first project:</p>

        <ol class="list-decimal ml-4">
            <li class="mb-4">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-bold">
                        <a href="{{ route('google-projects.index') }}">Connect your Google Cloud project</a>
                    </h3>
                    <x-status :status="auth()->user()->currentTeam->googleProjects()->count() ? 'Done' : 'Not Started'"></x-status>
                </div>
                <p>By connecting your Google Cloud project, Rafter can enable APIs and start preparing to deploy your first application.</p>
            </li>
            <li class="mb-4">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-bold">
                        <a href="{{ $githubAppUrl }}">Connect your GitHub Account</a>
                    </h3>
                    <x-status :status="auth()->user()->sourceProviders()->count() ? 'Done' : 'Not Started'"></x-status>
                </div>
                <p>By connecting your GitHub account, Rafter can deploy your application source code quickly and securely.</p>
            </li>
            <li>
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-bold">
                        <a href="{{ route('projects.create') }}">Create your first Rafter project</a>
                    </h3>
                    <x-status status="Not Started"></x-status>
                </div>
                <p>When you're ready, create your first Rafter project to deploy your app to Google Cloud.</p>
            </li>
        </ol>
    </x-card>
    @endif
</x-layout>
