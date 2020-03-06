<x-layout>
    <x-environment :project="$project" :environment="$environment">
        <x-slot name="title">Deployments</x-slot>

        <p class="mb-4 text-gray-600">Every time you push code, Rafter will automatically deploy a revision of your application to this environment. You can optionally disable auto-deploy or change the branch that is deployed under Settings.</p>

        @foreach ($environment->deployments as $deployment)
            <x-item
                :link="route('projects.environments.deployments.show', [$project, $environment, $deployment])"
                list="true"
            >
                <x-slot name="title">
                    <div class="flex justify-start">
                        <span class="mr-4">{{ $deployment->commit_message }}</span>
                        @include('components.status', ['status' => $deployment->status])
                    </div>
                </x-slot>
                <x-slot name="meta">
                    Deployed to <b>{{ $environment->name }}</b> by <b>{{ $deployment->initiator->name }}</b>
                </x-slot>
                <x-slot name="status">
                    {{ $deployment->created_at->diffForHumans() }}
                </x-slot>
            </x-item>
        @endforeach
    </x-environment>
</x-layout>
