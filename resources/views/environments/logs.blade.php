<x-layout>
    <x-environment :project="$project" :environment="$environment">
        <x-slot name="title">Logs</x-slot>

        <p class="mb-4 text-gray-600">View the logs from your service.</p>

        <livewire:log-viewer :environment="$environment" />
    </x-environment>
</x-layout>
