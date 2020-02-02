@extends('layouts.app')

@section('content')
@component('components.title')
    <h1>Google Cloud Projects</h1>
@endcomponent

@if (session('status'))
    <div class="text-sm border border-t-8 rounded text-green-700 border-green-600 bg-green-100 px-3 py-4 mb-4" role="alert">
        {{ session('status') }}
    </div>
@endif

@if (count($googleProjects) > 0)
    @component('components.card')
        @slot('title')
            Your Google Projects
        @endslot

        @foreach ($googleProjects as $project)
            <h2 class="flex items-center justify-between">
                <span>{{ $project->name }}</span>
                @include('components.status', ['status' => $project->status])
            </h2>
        @endforeach
    @endcomponent
@endif

@component('components.card', ['classes' => 'md:w-3/4'])
    @slot('title')
        <h2>Add a Google Cloud Project</h2>
    @endslot

    <p class="mb-4">By connecting a <a href="https://console.cloud.google.com">Google Cloud project</a>, Rafter can enable APIs and start preparing to deploy your first application.</p>

    <form action="{{ route('google-projects.store') }}" method="POST">
        @csrf

        @component('components.form.input', [
            'name' => 'name',
            'label' => 'Project Name',
            'required' => true,
        ])
            @slot('helper')
                <p>Enter a project name. It can contain numbers, letters and spaces.</p>
            @endslot
        @endcomponent
        @component('components.form.input', [
            'name' => 'project_id',
            'label' => 'Project ID',
            'required' => true,
        ])
            @slot('helper')
                <p>
                    Enter the project ID for your Google Project. This is usually <b>lowercase letters and numbers, separated by hyphens</b>.
                    Find it by clicking the Project selector dropdown in the Google Cloud web console.
                </p>
            @endslot
        @endcomponent
        @component('components.form.textarea', [
            'name' => 'service_account_json',
            'label' => 'Service Account JSON',
            'classes' => 'font-mono text-sm',
            'required' => true,
        ])
            @slot('helper')
                <p>
                    Create a <a href="https://console.cloud.google.com/iam-admin/serviceaccounts">service account</a> for your project.
                    Important: You must give the service account the <b>Project Owner</b> role in order for Rafter to function properly.
                </p>
            @endslot
        @endcomponent
        <div class="text-right">
            @component('components.button')
                Add Project
            @endcomponent
        </div>
    </form>
@endcomponent
@endsection
