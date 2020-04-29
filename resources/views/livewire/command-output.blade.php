<div wire:poll>
    <div class="flex justify-between items-center mb-4">
        <span class="text-sm">
            {{ $this->label }} {{ $command->updated_at->diffForHumans() }}
            @if ($command->isFinished())
                ({{ $command->elapsedTime() }})
            @endif
        </span>
        <span>
            <x-status :status="$command->status" />
        </span>
    </div>
    <div class="font-mono p-4 text-sm bg-white">
        <pre>{{ $output }}</pre>
    </div>
</div>
