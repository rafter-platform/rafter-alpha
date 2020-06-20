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
                required
                :options="$googleProjects->reduce(function ($memo, $p) {
                    $memo[$p->id] = $p->name;
                    return $memo;
                }, [])"
            />

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
                required
                :options="$types"
            />

            <x-select
                name="version"
                label="Database Version"
                required
                :options="$versions"
            />

            <x-select
                name="tier"
                label="Database Instance Tier"
                required
                :options="$tiers['mysql']"
            />

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
                required
                :options="$regions"
            />

            <div class="text-right">
                <x-button>Create Database Instance</x-button>
            </div>
        </form>
    </x-card>
</x-layout>
