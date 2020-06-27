<div>
    <div class="mb-4">
        <form wire:submit.prevent="runCommand">
            <label for="command" class="sr-only">
                Run a new command
            </label>
            <div class="mt-1 flex rounded-l-md shadow-sm">
                <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 border-r-0">
                    {{ $environment->project->commandPrefix() }}
                </span>
                <input wire:model="command" id="command" class="form-input flex-1 block w-full px-3 py-2 rounded-none sm:leading-5 autofocus" placeholder="command" />
                <span class="inline-flex rounded-r-md shadow-sm">
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent leading-5 font-medium rounded-r-md text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition ease-in-out duration-150">
                        Run
                    </button>
                </span>
            </div>
        </form>
    </div>
    <x-table>
        <x-slot name="thead">
            <x-th>
                Command
            </x-th>
            <x-th>
                Status
            </x-th>
            <x-th>
                When
            </x-th>
            <x-th>
                Initiated By
            </x-th>
            <x-th last />
        </x-slot>

        @foreach ($commands as $idx => $command)
            <x-tr>
                <x-td>
                    <a href="{{ $command->url() }}"><code>{{ $command->prefix() }} {{ $command->command }}</code></a>
                </x-td>
                <x-td>
                    <x-status :status="$command->status"></x-status>
                </x-td>
                <x-td>
                    {{ $command->created_at->diffForHumans() }}
                </x-td>
                <x-td>
                    {{ $command->user->name }}
                </x-td>
                <x-td last>
                    <x-text-link href="{{ $command->url() }}">
                        View
                    </x-text-link>
                </x-td>
            </x-tr>
        @endforeach

        <x-slot name="pagination">
            {{ $commands->links('pagination') }}
        </x-slot>
    </x-table-row>
</div>
