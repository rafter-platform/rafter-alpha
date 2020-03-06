<x-layout>
    <x-environment :project="$project" :environment="$environment">
        <x-slot name="title">Database</x-slot>

        <p class="mb-4 text-gray-600">
            When you connect a database to this environment, environment variables are automatically populated to allow
            your application to connect to it.
        </p>

        @if ($environment->database()->exists())
            <p>
                Connected to <b>{{ $environment->database->name }}</b> on <b>{{ $environment->database->databaseInstance->name }}</b>.
            </p>

            <form action="{{ route('projects.environments.database.destroy', [$project, $environment, $environment->database]) }}" method="POST">
                @csrf
                @method('DELETE')

                <div class="pt-4">
                    <x-button color="red">Disconnect Database</x-button>
                </div>
            </form>
        @else
            <x-card>
                <x-slot name="title">Connect Database</x-slot>

                <form action="{{ route('projects.environments.database.store', [$project, $environment]) }}" method="POST" x-data="{ method: 'new' }">
                    @csrf

                    <div class="flex mb-4">
                        <label class="mr-4">
                            <input type="radio" class="form-radio" name="method" value="new" @change="method = 'new'" checked>
                            <span class="ml-2">Create a new database in...</span>
                        </label>
                        <label>
                            <input type="radio" class="form-radio" name="method" value="existing" @change="method = 'existing'">
                            <span class="ml-2">Use an existing database...</span>
                        </label>
                    </div>

                    <x-select
                        x-show="method === 'new'"
                        name="database_instance_id"
                        label="Database Instance"
                        :options="$databaseInstances->mapWithKeys(function ($item) {
                            return [$item->id => $item->name];
                        })"
                    />

                    <x-select
                        x-show="method === 'existing'"
                        name="database_id"
                        label="Database"
                        :options="$databases->mapWithKeys(function ($item) {
                            return [$item->id => $item->name . ' ('. $item->databaseInstance->name . ')'];
                        })" />

                    <div class="text-right">
                        <x-button type="submit" x-text="method === 'new' ? 'Create and Assign Database' : 'Assign Database'" />
                    </div>
                </form>
            </x-card>
        @endif
    </x-environment>
</x-layout>
