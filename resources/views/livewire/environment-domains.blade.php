<div wire:poll>
    @if (count($mappings) > 0)
        <x-table>
            <x-slot name="thead">
                <x-th>Domain</x-th>
                <x-th>Status</x-th>
                <x-th>Instructions</x-th>
                <x-th last />
            </x-slot>

            @foreach ($mappings as $idx => $mapping)
                <x-tr :idx="$idx">
                    <x-td>{{ $mapping->domain }}</x-td>
                    <x-td><x-status :status="$mapping->status" /></x-td>
                    <x-td>
                        <div class="text-xs">
                            {!! $mapping->message !!}
                        </div>
                    </x-td>
                    <x-td last />
                </x-tr>
            @endforeach
        </x-table>
    @endif

    <h3 class="text-lg mb-2">Assign a Custom Domain</h3>

    <form wire:submit.prevent="addDomain">
        <x-input name="domain" label="Domain Name" wire:model="domain" />

        <div class="text-right">
            <x-button>Add Domain</x-button>
        </div>
    </form>
</div>
