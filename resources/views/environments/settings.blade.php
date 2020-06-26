<x-layout>
    <x-environment :project="$project" :environment="$environment">
        <livewire:environment-variables-settings :environment="$environment" />
        <x-settings-form-spacer />
        <livewire:environment-source-control-settings :environment="$environment" />
        <x-settings-form-spacer />
        <livewire:environment-service-settings :environment="$environment" type="web" />
        <x-settings-form-spacer />
        <livewire:environment-service-settings :environment="$environment" type="worker" />
    </x-environment>
</x-layout>
