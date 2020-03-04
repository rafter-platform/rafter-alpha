<x-layout>
    @include('environments._header')
    <x-flash />

    <div class="flex justify-between">
        <x-subtitle>Deployment Details</x-subtitle>
        <form action="{{ route('projects.environments.deployments.redeploy', [$project, $environment, $deployment]) }}" method="POST">
            @csrf
            <x-button>Redeploy</x-button>
        </form>
    </div>

    <livewire:deployment-status :deployment="$deployment" />
</x-layout>
