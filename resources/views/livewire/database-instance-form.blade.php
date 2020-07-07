<div>
    {{-- This form will be wrapped in a parent <form> in the context it's being consumed. --}}

    <x-radio-button-group label="What type of database?" name="type">
        <x-radio-button wire:model="type" name="type" value="mysql">MySQL</x-radio-button>
        <x-radio-button wire:model="type" name="type" value="postgres" disabled>Postgres</x-radio-button>
    </x-radio-button-group>

    <x-radio-button-group label="Which version?" name="version">
        @foreach ($versions as $key => $value)
            <x-radio-button wire:model="version" name="version" :value="$key">{{ $value }}</x-radio-button>
        @endforeach
    </x-radio-button-group>

    <x-radio-group label="Which tier?" name="tier">
        @foreach ($tiers as $key => $value)
            <x-radio wire:model="tier" name="tier" :value="$key" small>{{ $value }}</x-radio>
        @endforeach
    </x-radio-group>
</div>
