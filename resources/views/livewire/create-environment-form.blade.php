<x-settings-form title="Create new environment" action="Create Environment">
    <x-slot name="description">Add a new environment for {{ $project->name }}.</x-slot>

    <x-input label="Enviroment name" wire:model="name" name="name" required></x-input>
    <x-input label="Git branch" wire:model="branch" name="branch" required>
        <x-slot name="helper">Provide the Git branch within <code>{{ $project->repository }}</code> you'd like to connect to this environment.</x-slot>
    </x-input>
</x-settings-form>
