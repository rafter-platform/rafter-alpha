<div wire:poll.5s wire:init="loadLogs">
    <div class="flex md:justify-between text-sm mb-2 text-gray-600">
        <div>
            <label class="mr-1"><input type="radio" name="service" value="web" wire:model="service" wire:loading.attr="disabled"> Web</label>
            <label><input type="radio" name="service" value="worker" wire:model="service" wire:loading.attr="disabled"> Worker</label>
        </div>
        <div>
            <label class="mr-1"><input type="radio" name="logType" value="all" wire:model="logType" wire:loading.attr="disabled"> All logs</label>
            <label><input type="radio" name="logType" value="app" wire:model="logType" wire:loading.attr="disabled"> App logs only</label>
        </div>
    </div>
    <div class="font-mono p-4 bg-white text-xs overflow-y-scroll h-64 w-full block shadow">
        @foreach ($logs as $log)
            <p>{{ $log['text'] }}</p>
        @endforeach
    </div>
</div>
