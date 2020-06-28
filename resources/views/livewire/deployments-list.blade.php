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
            <x-tr>
                <x-td>
                    <div class="flex items-center">
                        <a class="max-w-md overflow-hidden whitespace-no-wrap block" style="text-overflow: ellipsis" href="{{ $deployment->getRoute() }}">{{ $deployment->commit_message }}</a>
                        @if ($deployment->isRedeploy())
                            <span class="inline-block ml-2 text-gray-600" title="Redeploy of Deployment #{{ $deployment->redeployment->id }}">
                                <x-heroicon-o-refresh class="w-4 h-4 stroke-current" aria-hidden="true" />
                                <span class="sr-only">Redeploy of Deployment #{{ $deployment->redeployment->id }}</span>
                            </span>
                        @endif
                    </div>
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
                    <x-text-link href="{{ $deployment->getRoute() }}">View</x-text-link>
                </x-td>
            </x-tr>
        @endforeach

        <x-slot name="pagination">
            {{ $deployments->links('pagination') }}
        </x-slot>
    </x-table>
</div>
