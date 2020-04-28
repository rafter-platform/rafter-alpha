<x-layout>
    <x-environment :project="$project" :environment="$environment">
        <div class="sm:flex items-center justify-between mb-4">
            <x-subtitle>Commands</x-subtitle>
            <x-white-button href="#">New Command</x-white-button>
        </div>

        <livewire:commands-list :environment="$environment" />
    </x-environment>
</x-layout>
