<x-layout>
    <x-card>
        <x-slot name="title">Projects</x-slot>

        @foreach ($projects as $project)
            <div>
                <a href="{{ route('projects.show', [$project]) }}">{{ $project->name }}</a>
            </div>
        @endforeach

        <div class="mt-4">
            <a href="{{ route('projects.create') }}">Create new project</a>
        </div>
    </x-card>
</x-layout>
