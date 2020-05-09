<x-layout>
    <x-environment :project="$project" :environment="$environment">
        <x-subtitle>Commands</x-subtitle>

        <x-description>Commands allow you to execute framework-specific operations on your worker service. Run a command below to see the results. Click on a previous command to see the prior results.</x-description>

        <livewire:commands-list :environment="$environment" />
    </x-environment>
</x-layout>
