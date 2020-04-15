<x-layout>
    <x-flash />
    <!-- TODO: Conditionally show form based on whether user has connected GitHub -->
    <x-card>
        <x-slot name="title"><h1>Create a Project</h1></x-slot>

        <form action="{{ route('projects.store') }}" method="POST" x-data="{ type: 'laravel' }">
            @csrf
            <x-input
                name="name"
                label="Project Name"
                required="true"
            />
            <x-select
                name="google_project_id"
                label="Google Project"
                required="true"
                :options="$googleProjects->reduce(function ($memo, $p) {
                    $memo[$p->id] = $p->name;
                    return $memo;
                }, [])"
            />
            <x-select
                name="type"
                label="Project Type"
                required="true"
                :options="$types"
                x-model="type"
            />
            <div x-show="type === 'laravel'" class="text-sm text-gray-600 mb-4">
                For Laravel projects, be sure add Rafter's core package with <code class="bg-gray-200 p-1">composer install rafter-platform/laravel-rafter-core</code> before deploying.
            </div>
            <x-select
                name="region"
                label="Region"
                required="true"
                :options="$regions"
            />
            <x-select
                name="source_provider_id"
                label="Deployment Source"
                required="true"
                :options="$sourceProviders->reduce(function ($memo, $p) {
                    $memo[$p->id] = $p->name;
                    return $memo;
                }, [])"
            />
            <x-input
                name="repository"
                label="GitHub Repository"
                required="true"
            />
            <div x-show="type === 'laravel'" class="text-sm text-gray-600 mb-4">
                Can't find the repository you're looking for? Be sure to <a href="{{ \App\Services\GitHubApp::installationUrl() }}" target="_blank">grant Rafter access to it in GitHub</a>.
            </div>
            <div class="text-right">
                <x-button>Create Project</x-button>
            </div>
        </form>
    </x-card>
</x-layout>
