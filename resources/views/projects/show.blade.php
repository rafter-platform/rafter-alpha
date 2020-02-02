@extends('layouts.app')

@section('content')
    <div class="mb-8 flex items-center justify-between border-b border-b-2 pb-4">
        @component('components.title', ['margin' => 'mb-0'])
            <h1>{{ $project->name }}</h1>
        @endcomponent

        <span class="text-sm uppercase text-gray-600">
            {{ $project->googleProject->name }} - {{ $project->region }}
        </span>
    </div>

    @if (session('status'))
    <div class="text-sm border border-t-8 rounded text-green-700 border-green-600 bg-green-100 px-3 py-4 mb-4" role="alert">
        {{ session('status') }}
    </div>
    @endif

    @include('components.subtitle', ['title' => 'Environments'])

    <p class="mb-4 text-gray-600">
        By default, production and staging environments are automatically created for your project. You can add additional environments and
        configure them to auto-deploy when you push commits to certain branches.
    </p>

    @foreach ($project->environments as $environment)
        @component('components.item', ['link' => route('projects.environments.show', [$project, $environment])])
            @slot('title')
                {{ $environment->name}}
            @endslot
            @slot('meta')
                {{ $environment->url ?? 'URL not yet available' }}
            @endslot
            @slot('status')
                Last deployed {{ $environment->deployments()->latest()->first()->created_at->diffForHumans() }}
            @endslot
        @endcomponent
    @endforeach
@endsection
