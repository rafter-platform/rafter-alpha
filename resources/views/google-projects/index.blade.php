@extends('layouts.app')

@section('content')
@if (session('status'))
    <div class="text-sm border border-t-8 rounded text-green-700 border-green-600 bg-green-100 px-3 py-4 mb-4" role="alert">
        {{ session('status') }}
    </div>
@endif

@component('components.card')
    @slot('title')
        Your Google Projects
    @endslot

    @foreach ($googleProjects as $project)
        <h2>{{ $project->name }}</h2>
    @endforeach

    @unless (count($googleProjects) > 0)
        <h2>You don't have any projects</h2>
    @endunless
@endcomponent

@component('components.card')
    @slot('title')
        <h2>Add a project</h2>
    @endslot

    <form action="{{ route('google-projects.store') }}" method="POST">
        @csrf

        <div>
            <label for="name">Project Name</label>
            <input type="text" id="name" name="name">
        </div>
        <div>
            <label for="project_id">Project ID</label>
            <input type="text" id="project_id" name="project_id">
        </div>
        <div>
            <label for="service_account_json">Service Account JSON</label>
            <textarea id="service_account_json" name="service_account_json"></textarea>
        </div>
        <div>
            <button type="submit">Add Project</button>
        </div>
    </form>
@endcomponent
@endsection
