<x-layout>
    <x-environment :project="$project" :environment="$environment">
        <livewire:environment-metrics :environment="$environment" />
        <livewire:deployments-list :environment="$environment" />
    </x-environment>
</x-layout>
