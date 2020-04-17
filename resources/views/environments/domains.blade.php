<x-layout>
    <x-environment :project="$project" :environment="$environment">
        <x-slot name="title">Domains</x-slot>

        <p class="mb-4 text-gray-600">Assign one or more vanity domains to your service.</p>

        <livewire:environment-domains :environment="$environment" />
    </x-environment>
</x-layout>
