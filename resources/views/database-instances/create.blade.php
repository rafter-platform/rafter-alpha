<x-layout>
    <x-title>
        <h2>Create a Database Instance</h2>
    </x-title>

    <x-card>
        <form action="{{ route('database-instances.store') }}" method="POST">
            @csrf

            <x-select
                name="google_project_id"
                label="Google Project"
                required>
                @foreach ($googleProjects as $project)
                    <option :value="$project->id">{{ $project->name }}</option>
                @endforeach
            </x-select>

            <x-input
                name="name"
                label="Database Instance Name"
                required
            >
                <x-slot name="helper">
                    Name may only contain lowercase letters and hyphens.
                </x-slot>
            </x-input>

            <x-select
                name="type"
                label="Database Type"
                required>
                @foreach ($types as $type)
                    <option :value="$type">{{ $type }}</option>
                @endforeach
            </x-select>

            <x-select
                name="version"
                label="Database Version"
                required>
                @foreach ($versions as $version)
                    <option :value="$version">{{ $version }}</option>
                @endforeach
            </x-select>

            <x-select
                name="tier"
                label="Database Instance Tier"
                required>
                @foreach ($tiers['mysql'] as $tier)
                    <option :value="$tier">{{ $tier }}</option>
                @endforeach
            </x-select>

            <x-input
                name="size"
                label="Database Disk Size (GB)"
                value="10"
                min="10"
                type="number"
                required
            />

            <x-select
                name="region"
                label="Region"
                required>
                @foreach ($regions as $region)
                    <option :value="$region">{{ $region }}</option>
                @endforeach
            </x-select>

            <div class="text-right">
                <x-button>Create Database Instance</x-button>
            </div>
        </form>
    </x-card>
</x-layout>
