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
        <h2>{{ $project->name }}: {{ $project->status }}</h2>
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

        @include('components.form.input', [
            'name' => 'name',
            'label' => 'Project Name',
            'required' => true,
        ])
        @include('components.form.input', [
            'name' => 'project_id',
            'label' => 'Project ID',
            'required' => true,
        ])
        @include('components.form.textarea', [
            'name' => 'service_account_json',
            'label' => 'Service Account JSON',
            'required' => true,
        ])
        <div class="text-right">
            @component('components.button')
                Add Project
            @endcomponent
        </div>
    </form>
@endcomponent
@endsection
