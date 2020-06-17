<div wire:poll>
    <div class="flex justify-between items-center">
        <x-subtitle>Recent Deployments</x-subtitle>
        <x-button wire:click="deployNow">Deploy Now</x-button>
    </div>
    <x-table>
        <x-slot name="thead">
            <x-th>
                Commit
            </x-th>
            <x-th>
                Status
            </x-th>
            <x-th>
                When
            </x-th>
            <x-th>
                Duration
            </x-th>
            <x-th>
                Initiated By
            </x-th>
            <x-th last />
        </x-slot>

        @foreach ($deployments as $idx => $deployment)
            <x-tr :idx="$idx">
                <x-td>
                    <a href="{{ $deployment->getRoute() }}">{{ $deployment->commit_message }}</a>
                </x-td>
                <x-td>
                    <x-status :status="$deployment->status" />
                </x-td>
                <x-td>
                    {{ $deployment->created_at->diffForHumans() }}
                </x-td>
                <x-td>
                    {{ $deployment->isInProgress() ? '-' : $deployment->duration() }}
                </x-td>
                <x-td>
                    {{ $deployment->initiator->name }}
                </x-td>
                <x-td last>
                    <a href="{{ $deployment->getRoute() }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                </x-td>
            </x-tr>
        @endforeach

        <x-slot name="pagination">
            {{ $deployments->links('pagination') }}
        </x-slot>
    </x-table>
</div>
