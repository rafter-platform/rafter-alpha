<x-layout>
    <x-title>
        <h1>Databases</h1>
    </x-title>

    <p class="text-gray-800 mb-4">
        Database instances represent <a href="https://console.cloud.google.com/sql/instances">Cloud SQL instances</a> in your Google Project.
        You can create new instances or manage existing instances inside Rafter.
    </p>

    <div class="mb-6">
        @foreach ($databaseInstances as $instance)
            <x-item :link="route('database-instances.show', [$instance])">
                <x-slot name="title">
                    <div class="flex justify-start">
                        <span class="mr-4">{{ $instance->name }}</span>
                        <x-status :status="$instance->status"></x-status>
                    </div>
                </x-slot>
                <x-slot name="meta">
                    {{ $instance->region }} / {{ $instance->tier }} / {{ $instance->size }}GB / {{ $instance->databases()->count() }} databases
                </x-slot>
            </x-item>
        @endforeach
    </div>

    <x-button :href="route('database-instances.create')">Create Database</x-button>
</x-layout>
