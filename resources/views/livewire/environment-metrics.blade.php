<div class="mb-8" wire:init="loadMetrics">
    <div class="flex items-center justify-between">
        <h3 class="text-xl leading-6 font-medium text-gray-900">
            Past {{ ucfirst($this->duration) }}
        </h3>
        <x-dropdown-menu label="Past {{ ucfirst($duration) }}">
            @foreach (array_keys($this->durations) as $duration)
                <x-dropdown-menu-item wire:click="setDuration('{{ $duration }}')">Past {{ ucfirst($duration) }}</x-dropdown-menu-item>
            @endforeach
        </x-dropdown-menu>
    </div>
    <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-3">
        <x-metric title="Total Requests">{{ $totalRequests }}</x-metric>
        <x-metric title="Web Requests">{{ $webRequests }}</x-metric>
        <x-metric title="Worker Requests">{{ $workerRequests }}</x-metric>
    </div>
</div>
