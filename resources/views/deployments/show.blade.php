<x-layout>
    <x-environment :project="$project" :environment="$environment">
        <div class="flex justify-between">
            <x-subtitle>Deployment Details</x-subtitle>
            <form action="{{ route('projects.environments.deployments.redeploy', [$project, $environment, $deployment]) }}" method="POST">
                @csrf
                <x-button>Redeploy</x-button>
            </form>
        </div>
        <div class="overflow-hidden shadow rounded-lg">
            <livewire:deployment-status :deployment="$deployment" />
        </div>
    </x-environment>
</x-layout>
