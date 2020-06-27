<div wire:poll>
    <form class="mb-4" wire:submit.prevent="addDomain">
        <div class="flex mt-1">
            <div class="relative rounded-l-md shadow-sm flex-1">
                <label for="domain" class="sr-only">Add a domain</label>
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="text-gray-500">
                        https://
                    </span>
                </div>
                <input name="domain" label="Domain Name" wire:model="domain" class="form-input rounded-r-none block w-full pl-20" placeholder="www.example.com" />
            </div>
            <span class="inline-flex rounded-r-md shadow-sm">
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent leading-5 font-medium rounded-r-md text-white bg-blue-600 hover:bg-blue-500 focus:outline-none focus:border-blue-700 focus:shadow-outline-blue active:bg-blue-700 transition ease-in-out duration-150">
                    Add Domain
                </button>
            </span>
        </div>
        @error('domain')
            <p class="text-red-500 text-xs italic mt-4">
                {{ $message }}
            </p>
        @enderror
    </form>
    @if (count($mappings) > 0)
        <x-table>
            <x-slot name="thead">
                <x-th>Domain</x-th>
                <x-th>Status</x-th>
                <x-th>Instructions</x-th>
                <x-th last />
            </x-slot>

            @foreach ($mappings as $idx => $mapping)
                <x-tr>
                    <x-td class="font-medium">
                        <a href="https://{{ $mapping->domain }}" target="_blank">{{ $mapping->domain }}</a>
                    </x-td>
                    <x-td><x-status :status="$mapping->status" /></x-td>
                    <x-td>
                        <div class="text-xs whitespace-normal prose">
                            {!! $mapping->message !!}
                        </div>
                        @if ($mapping->isUnverified())
                            <div class="mt-4">
                                <x-button wire:click="verifyDomain({{ $mapping->id }})">
                                    <x-heroicon-o-check-circle class="w-5 h-5 mr-1 text-green-600" />
                                    I have added the service account
                                </x-button>
                            </div>
                        @endif
                    </x-td>
                    <x-td last>
                        <div class="lg:hidden text-xs">
                            @if ($mapping->canManuallyCheckStatus())
                                <button wire:click="checkDomainStatus({{ $mapping->id }})">Check Status</button>
                            @endif
                            <button class="mr-2" wire:click="deleteDomain({{ $mapping->id }})">Delete</button>
                        </div>
                        <div class="hidden lg:block">
                            <x-dropdown-menu>
                                @if ($mapping->canManuallyCheckStatus())
                                    <x-dropdown-menu-item wire:click="checkDomainStatus({{ $mapping->id }})">Check Status</x-dropdown-menu-item>
                                @endif
                                <x-dropdown-menu-item wire:click="deleteDomain({{ $mapping->id }})">Delete</x-dropdown-menu-item>
                            </x-dropdown-menu>
                        </div>
                    </x-td>
                </x-tr>
            @endforeach
        </x-table>
    @endif
</div>
