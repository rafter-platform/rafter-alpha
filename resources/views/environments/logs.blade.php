<x-layout>
    <x-environment :project="$project" :environment="$environment">
        <x-slot name="title">Logs</x-slot>

        <p class="mb-4 text-gray-600">
            View the logs from your service, or view all logs by visiting your
            <a class="text-blue-600 underline" href="https://console.cloud.google.com/run?project={{ $environment->projectId() }}" target="_blank" rel="noopener">Cloud Run dashboard</a>.
        </p>

        <livewire:log-viewer :environment="$environment" />
    </x-environment>
</x-layout>
