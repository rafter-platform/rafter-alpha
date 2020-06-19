<x-settings-form>
    <x-slot name="title">{{ ucfirst($type) }} Service</x-slot>
    <x-slot name="description">
        <p>Modify the settings that control your Cloud Run {{ $type }} service.</p>
    </x-slot>

    <x-label for="{{ $type }}_memory">Memory allocated</x-label>
    <x-radio-button-group name="memory">
        <x-slot name="helper">Memory to allocate to each container instance.</x-slot>
        @foreach ($memoryOptions as $key => $option)
            <x-radio-button wire:model="memory" :value="$key" name="{{ $type }}_memory">{{ $option }}</x-radio-button>
        @endforeach
    </x-radio-button-group>

    <x-label for="{{ $type }}_cpu">CPUs allocated</x-label>
    <x-radio-button-group name="cpu">
        <x-slot name="helper">Number of vCPUs allocated to each container instance</x-slot>
        @foreach ([1, 2] as $option)
            <x-radio-button wire:model="cpu" :value="$option" name="{{ $type }}_cpu">{{ $option }} vCPU{{ $option > 1 ? 's' : '' }}</x-radio-button>
        @endforeach
    </x-radio-button-group>

    <x-input type="number" max="900" min="1" label="Request timeout" name="requestTimeout" wire:model="requestTimeout">
        <x-slot name="helper">Time within which a response must be returned (maximum 900 seconds).</x-slot>
    </x-input>

    <x-input type="number" max="80" min="1" label="Max requests per container" name="maxRequestsPerContainer" wire:model="maxRequestsPerContainer">
        <x-slot name="helper">The maximum number of concurrent requests that can reach each container instance.</x-slot>
    </x-input>

    <x-input type="number" max="1000" min="1" label="Maximum number of instances" name="maxInstances" wire:model="maxInstances">
        <x-slot name="helper">The maximum number of container instances Cloud Run will use to autoscale your service.</x-slot>
    </x-input>
</x-settings-form>
