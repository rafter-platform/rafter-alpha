<div wire:poll>
    <div class="bg-white">
        <div class="p-4 border-b">
            <div class="flex items-center mb-1">
                <h1 class="text-xl mr-4">{{ $deployment->commit_message }}</h1>
                <x-status :status="$deployment->status" />
            </div>
            <p class="text-sm text-gray-600">
                Deployed to <b>{{ $deployment->environment->name }}</b> by <b>{{ $deployment->initiator->name }}</b>
            </p>
        </div>
        @foreach ($deployment->steps as $step)
            <div class="border-b" @if ($step->hasFailed()) x-data="{ showLogs: true }" @else x-data="{ showLogs: false }" @endif>
                <div class="flex justify-between items-center p-2 px-4">
                    <div class="flex items-center">
                        <span class="text-gray-700 text-sm">{{ $step->label() }}</span>
                        @if ($step->output)
                            <button @click="showLogs = !showLogs" class="ml-2 text-gray-600" title="Inspect output">
                                <x-heroicon-s-eye class="fill-current w-4 h-4" />
                            </button>
                        @endif
                    </div>

                    <div class="flex items-center">
                        <span class="text-sm mr-2 text-gray-600">{{ $step->duration() }}</span>
                        <x-status :status="$step->status" />
                    </div>
                </div>
                @if ($step->output)
                    <pre x-show="showLogs" class="bg-gray-100 text-xs text-gray-600 p-2 px-4 max-h-80 overflow-auto build-logs">{!! $step->output !!}</pre>
                @endif
            </div>
        @endforeach
    </div>
</div>
