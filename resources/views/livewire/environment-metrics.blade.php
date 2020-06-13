<div class="mb-8" wire:init="loadMetrics">
    <h3 class="text-xl leading-6 font-medium text-gray-900">
        Last 24 hours
    </h3>
    <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-3">
        <x-metric title="Total Requests">{{ $totalRequests }}</x-metric>
        <x-metric title="Web Requests">{{ $webRequests }}</x-metric>
        <x-metric title="Worker Requests">{{ $workerRequests }}</x-metric>
    </div>
</div>
