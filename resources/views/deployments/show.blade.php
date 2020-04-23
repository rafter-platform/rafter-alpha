<x-layout>
    <x-environment :project="$project" :environment="$environment">
        <x-card>
            <div class="flex justify-between">
                <x-subtitle>Deployment Details</x-subtitle>
                <form action="{{ route('projects.environments.deployments.redeploy', [$project, $environment, $deployment]) }}" method="POST">
                    @csrf
                    <x-button>Redeploy</x-button>
                </form>
            </div>
            <livewire:deployment-status :deployment="$deployment" />
        </x-card>
    </x-environment>
</x-layout>
