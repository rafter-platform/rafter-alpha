<x-layout>
    <x-title><h1>Source Providers</h1></x-title>

    <p class="mb-4 text-gray-600">
        Source Providers like GitHub allow Rafter to connect to your code and deploy it to the cloud. View existing source provider
        installations below, and create new installations by clicking the button.
    </p>

    @foreach ($sources as $source)
        <x-item :link="route('source-providers.edit', [$source])">
            <x-slot name="title">{{ $source->name }}</x-slot>
            <x-slot name="status">Created {{ $source->created_at->diffForHumans() }}</x-slot>
        </x-item>
    @endforeach

    <div class="mt-8">
        <x-button :href="\App\Services\GitHubApp::installationUrl()">Create New GitHub Installation</x-button>
    </div>
</x-layout>
