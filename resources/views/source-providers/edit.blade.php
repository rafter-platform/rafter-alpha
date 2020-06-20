<x-layout>
    <x-card>
        <x-slot name="title"><h1>Edit GitHub Installation</h1></x-slot>

        <form action="{{ route('source-providers.update', [$source]) }}" method="POST">
            @csrf
            @method('PUT')

            <x-input
                name="name"
                label="Installation Name"
                required="true"
                :value="$source->name"
            >
                <x-slot name="helper">
                    <p>Be sure to give your installation a unique name, e.g. <b>Acme Co. GitHub</b> or <b>Personal GitHub</b>.</p>
                </x-slot>
            </x-input>
            <x-input
                name="installation_id"
                label="Installation ID"
                disabled="true"
                :value="$source->installation_id"
            />
            <x-textarea
                name="repos"
                label="Available Repositories"
                disabled="true"
                :value="$repos"
            >
                <x-slot name="helper">
                    <p>Want to add or remove available repositories? <a href="{{ \App\Services\GitHubApp::installationUrl($source->installation_id) }}" target="_blank">Edit this installation</a>
                </x-slot>
            </x-textarea>
            <div class="text-right">
                <x-button>Update</x-button>
            </div>
        </form>
    </x-card>
</x-layout>
