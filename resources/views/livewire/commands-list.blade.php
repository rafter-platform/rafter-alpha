<div>
    <div class="mb-4">
        <form wire:submit.prevent="runCommand">
            <label for="command" class="sr-only">
                Run a new command
            </label>
            <div class="mt-1 flex rounded-l-md shadow-sm">
                <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 border-r-0">
                    php artisan
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
    <div class="flex flex-col">
        <div class="-my-2 py-2 overflow-x-auto sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
            <div class="align-middle inline-block min-w-full shadow overflow-hidden sm:rounded-lg border-b border-gray-200">
                <table class="min-w-full">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                Command
                            </th>
                            <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                When
                            </th>
                            <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                Initiated By
                            </th>
                            <th class="px-6 py-3 border-b border-gray-200 bg-gray-50"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($commands as $idx => $command)
                            <tr class="{{ $idx % 2 == 0 ? 'bg-white' : 'bg-gray-50' }}">
                                <td class="px-6 py-4 whitespace-no-wrap text-sm leading-5 font-medium text-gray-900">
                                    <a href="{{ $command->url() }}"><code>php artisan {{ $command->command }}</code></a>
                                </td>
                                <td class="px-6 py-4 whitespace-no-wrap text-sm leading-5 text-gray-900">
                                    <x-status :status="$command->status"></x-status>
                                </td>
                                <td class="px-6 py-4 whitespace-no-wrap text-sm leading-5 text-gray-900">
                                    {{ $command->created_at->diffForHumans() }}
                                </td>
                                <td class="px-6 py-4 whitespace-no-wrap text-sm leading-5 text-gray-900">
                                    {{ $command->user->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-no-wrap text-sm text-right leading-5 font-medium text-gray-900">
                                    <a class="text-indigo-600 hover:text-indigo-900" href="{{ $command->url() }}">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $commands->links('pagination') }}
            </div>
        </div>
    </div>
</div>
