<div>
    @if ($environment->domainMappings()->count() > 0)
        <ul class="mb-4">
            @foreach ($environment->domainMappings as $mapping)
                <li>{{ $mapping->domain }} - <x-status :status="$mapping->status" /></li>
            @endforeach
        </ul>
    @endif

    <h3 class="text-lg mb-2">Assign a Custom Domain</h3>

    <form wire:submit.prevent="addDomain">
        <x-input name="domain" label="Domain Name" wire:model="domain" />

        <div class="text-right">
            <x-button>Add Domain</x-button>
        </div>
    </form>
</div>
