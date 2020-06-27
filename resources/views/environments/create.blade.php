<x-layout>
    <x-slot name="title">{{ $project->name }}</x-slot>

    <livewire:create-environment-form :project="$project" />
</x-layout>
