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

    <x-radio-group label="Which tier?" name="tier">
        @foreach ($tiers as $key => $value)
            <x-radio wire:model="tier" name="tier" :value="$key" small>{{ $value }}</x-radio>
        @endforeach
    </x-radio-group>

    <x-input wire:model="name" name="name" label="Give your database instance a name" />

    <div class="text-right">
        <x-button type="submit" wire:click.prevent="create">Create Database Instance</x-button>
    </div>
</div>
