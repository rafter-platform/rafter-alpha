@extends('layouts.app')

@section('content')
    @component('components.title')
        <h1>{{ auth()->user()->currentTeam->name }} Dashboard</h1>
    @endcomponent

    @include('components.flash')

    @if (auth()->user()->currentTeam->projects()->count())
        @component('components.subtitle')
            Projects
        @endcomponent

        @foreach (Auth::user()->currentTeam->projects as $project)
            @component('components.item', ['link' => route('projects.show', $project)])
                @slot('title')
                    {{ $project->name }}
                @endslot
                @slot('meta')
                    {{ \App\Project::TYPES[$project->type] }} / {{ $project->region }} / {{ $project->googleProject->name }}
                @endslot
                @slot('status')
                    Last deployed {{ $project->environments()->first()->activeDeployment()->created_at->diffForHumans() }}
                @endslot
            @endcomponent
        @endforeach
    @else
    @component('components.card', ['classes' => 'md:w-1/2'])
        @slot('title')
            <h2>Rafter Onboarding</h2>
        @endslot

        <p class="mb-6">To get started with Rafter, complete the following steps to deploy your first project:</p>

        <ol class="list-decimal ml-4">
            <li class="mb-4">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-bold">
                        <a href="{{ route('google-projects.index') }}">Connect your Google Cloud project</a>
                    </h3>
                    @include('components.status', ['status' => auth()->user()->currentTeam->googleProjects()->count() ? 'Done' : 'Not Started'])
                </div>
                <p>By connecting your Google Cloud project, Rafter can enable APIs and start preparing to deploy your first application.</p>
            </li>
            <li class="mb-4">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-bold">
                        <a href="{{ $githubAppUrl }}">Connect your GitHub Account</a>
                    </h3>
                    @include('components.status', ['status' => auth()->user()->sourceProviders()->count() ? 'Done' : 'Not Started'])
                </div>
                <p>By connecting your GitHub account, Rafter can deploy your application source code quickly and securely.</p>
            </li>
            <li>
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-bold">
                        <a href="{{ route('projects.create') }}">Create your first Rafter project</a>
                    </h3>
                    @include('components.status', ['status' => 'Not Started'])
                </div>
                <p>When you're ready, create your first Rafter project to deploy your app to Google Cloud.</p>
            </li>
        </ol>
    @endcomponent
    @endif
@endsection
