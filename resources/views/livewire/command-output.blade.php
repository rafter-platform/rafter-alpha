<div wire:poll>
    <x-subtitle>Command: <code>php artisan {{ $command->command }}</code></x-subtitle>
    <div class="flex justify-between items-center mb-2">
        <div class="text-sm">
            <x-status :status="$command->status" />
            <span class="ml-2">
                {{ $command->updated_at->diffForHumans() }}
                @if ($command->isFinished())
                    ({{ $command->elapsedTime() }})
                @endif
            </span>
        </div>
        <div class="flex">
            <x-white-button :href="$this->backLink">
                &larr; Back
            </x-white-button>
            <span class="ml-2 inline-flex rounded-md shadow-sm">
                <button wire:click="reRun" type="button" class="inline-flex items-center px-4 py-2 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition ease-in-out duration-150">
                    <x-heroicon-o-refresh class="-ml-1 mr-2 h-5 w-5"></x-heroicon-o-refresh>
                    Rerun
                </button>
            </span>
        </div>
    </div>
    <div class="font-mono p-4 text-sm bg-white border overflow-auto">
        <pre>{{ $output }}</pre>
    </div>
</div>
