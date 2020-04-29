<x-layout>
    <x-environment :project="$project" :environment="$environment">
        <x-subtitle>Command: <code>php artisan {{ $command->command }}</code></x-subtitle>

        <livewire:command-output :command="$command" />
    </x-environment>
</x-layout>
