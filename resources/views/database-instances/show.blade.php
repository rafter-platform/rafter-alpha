<x-layout>
    <x-title>
        <div class="border-b-2">
            <div class="mb-4">
                <a href="{{ route('database-instances.index') }}">Databases</a> /
                {{ $instance->name }}
            </div>
            <div class="flex items-center mb-4">
                <div class="text-sm text-gray-800 mr-4">
                    {{ $instance->region }} / {{ $instance->tier }} / {{ $instance->size }}GB
                </div>
                <x-status :status="$instance->status"></x-status>
            </div>
        </div>
    </x-title>

    <x-card>
        <x-slot name="title">Databases in {{ $instance->name }}</x-slot>

        <ul>
            @foreach ($instance->databases as $database)
                <li>{{ $database->name }}</li>
            @endforeach
        </ul>
    </x-card>
</x-layout>
