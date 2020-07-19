<div>
    <x-radio-button-group label="What type of database?" name="type">
        <x-radio-button wire:model="type" name="type" value="mysql" small>MySQL</x-radio-button>
        <x-radio-button wire:model="type" name="type" value="postgres" disabled small>Postgres</x-radio-button>
    </x-radio-button-group>

    <x-radio-button-group label="Which version?" name="version">
        @foreach ($versions as $key => $value)
            <x-radio-button wire:model="version" name="version" :value="$key" small>{{ $value }}</x-radio-button>
        @endforeach
    </x-radio-button-group>

    <x-select label="Select a tier" name="tier" wire:model="tier">
        @foreach ($tiers as $key => $value)
            <option value="{{ $key }}">{{ $value }}</option>
        @endforeach
    </x-select>

    <x-radio-button-group name="databaseGoogleProjectId" label="Which Google Cloud project?">
        @foreach ($projects as $project)
        <x-radio-button wire:model="databaseGoogleProjectId" name="databaseGoogleProjectId" value="{{ $project->id }}" small>
            <x-slot name="icon">
                <x-icon-google-cloud class="text-current w-6 h-6" />
            </x-slot>
            {{ $project->project_id }}
        </x-radio-button>
        @endforeach
    </x-radio-button-group>

    <div>
        @if ($databaseGoogleProjectId)
            <x-radio-button-group name="databaseRegion" label="Which Google Cloud region?">
                @foreach ($regions as $key => $region)
                <x-radio-button wire:model="databaseRegion" name="databaseRegion" value="{{ $key }}" small>
                    {{ $region }} ({{ $key }})
                </x-radio-button>
                @endforeach
            </x-radio-button-group>
        @endif
    </div>

    <div>
        @if ($databaseRegion)
            <x-input wire:model="name" name="name" label="Give your database instance a name" />
        @endif
    </div>

    @error('databaseInstance')
        <x-validation-error>
            {{ $message }}
        </x-validation-error>
    @enderror

    <div class="text-right">
        <x-button
            wire:click.prevent="create"
            wire:loading.attr="disabled"
            wire:target="create">
            Create Database Instance
        </x-button>
    </div>
</div>
