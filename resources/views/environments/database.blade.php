@extends('layouts.app')

@section('content')
    @component('environments._content', ['project' => $project, 'environment' => $environment])
        @slot('title')
            Database
        @endslot

        <p class="mb-4 text-gray-600">
            When you connect a database to this environment, environment variables are automatically populated to allow
            your application to connect to it.
        </p>

        @if ($environment->database()->exists())
            <p>
                Connected to <b>{{ $environment->database->name }}</b> on <b>{{ $environment->database->databaseInstance->name }}</b>.
            </p>

            <form action="{{ route('projects.environments.database.delete', [$project, $environment]) }}" method="POST">
                @csrf
                @method('DELETE')

                <div class="pt-4">
                    @component('components.button', ['color' => 'red'])
                        Disconnect Database
                    @endcomponent
                </div>
            </form>
        @else
            @component('components.card')
                @slot('title')
                    Connect Database
                @endslot

                <form action="{{ route('projects.environments.database.update', [$project, $environment]) }}" method="POST" x-data="{ method: 'new' }">
                    @csrf
                    @method('PUT')

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

                    <div x-show="method === 'new'">
                    @include('components.form.select', [
                        'name' => 'database_instance_id',
                        'label' => 'Database Instance',
                        'options' => $databaseInstances->mapWithKeys(function ($item) {
                            return [$item->id => $item->name];
                        }),
                    ])
                    </div>

                    <div x-show="method === 'existing'">
                    @include('components.form.select', [
                        'name' => 'database_id',
                        'label' => 'Database',
                        'options' => $databases->mapWithKeys(function ($item) {
                            return [$item->id => $item->name . " (" . $item->databaseInstance->name . ")"];
                        }),
                    ])
                    </div>

                    <div class="text-right">
                        <button class="button" type="submit" x-text="method === 'new' ? 'Create and Assign Database' : 'Assign Database'"></button>
                    </div>
                </form>
            @endcomponent
        @endif
    @endcomponent
@endsection
