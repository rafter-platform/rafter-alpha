<x-layout>
    <x-environment :project="$project" :environment="$environment">
        <livewire:environment-service-settings :environment="$environment" type="web" />
        <x-settings-form-spacer />
        <livewire:environment-service-settings :environment="$environment" type="worker" />
        <x-settings-form-spacer />
        <x-slot name="title">Settings</x-slot>

        <p class="mb-4 text-gray-600">
            Lorem ipsum, dolor sit amet consectetur adipisicing elit. Voluptatum debitis voluptates suscipit nam laudantium perferendis beatae id distinctio quia numquam ipsum, eius aliquid. Nesciunt accusantium unde ullam iusto distinctio eius.
        </p>

        <x-card>
            <x-slot name="title">Environment Variables</x-slot>

            <form action="{{ route('projects.environments.settings.store', [$project, $environment]) }}" method="POST">
                @csrf
                <x-textarea
                    name="environmental_variables"
                    label="Environment Variables"
                    classes="font-mono"
                    :value="$environment->environmental_variables"
                />
                <div class="text-right">
                    <x-button>Update Environment Variables and Deploy</x-button>
                </div>
            </form>
        </x-card>
    </x-environment>
</x-layout>
