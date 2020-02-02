@component('components.title')
    <div class="flex items-center justify-between pb-4 border-b-2">
        <div>
            <h1 class="mb-2">
                <a href="{{ route('projects.show', [$project]) }}">{{ $project->name }}</a>
                /
                <a href="{{ route('projects.environments.show', [$project, $environment]) }}">{{ $environment->name }}</a>
            </h1>
            <a class="text-gray-800 text-sm" href="{{ $environment->url }}" target="_blank">{{ $environment->url }}</a>
        </div>
    </div>
@endcomponent
