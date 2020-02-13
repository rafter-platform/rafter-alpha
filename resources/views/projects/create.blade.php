@extends('layouts.app')

@section('content')

@include('components.flash')

<!-- TODO: Conditionally show form based on whether user has connected GitHub -->
@component('components.card')

    @slot('title')
        <h1>Create a Project</h1>
    @endslot

    <form action="{{ route('projects.store') }}" method="POST" x-data="{ type: 'laravel' }">
        @csrf
        @include('components.form.input', [
            'name' => 'name',
            'label' => 'Project Name',
            'required' => true,
        ])
        @include('components.form.select', [
            'name' => 'google_project_id',
            'label' => 'Google Project',
            'required' => true,
            'options' => $googleProjects->reduce(function ($memo, $p) {
                $memo[$p->id] = $p->name;
                return $memo;
            }, [])
        ])
        @include('components.form.select', [
            'name' => 'type',
            'label' => 'Project Type',
            'required' => true,
            'options' => $types,
            'xModel' => 'type'
        ])
        <div x-show="type === 'laravel'" class="text-sm text-gray-600 mb-4">
            For Laravel projects, be sure add Rafter's core package with <code class="bg-gray-200 p-1">composer install rafter-platform/laravel-rafter-core</code> before deploying.
        </div>
        @include('components.form.select', [
            'name' => 'region',
            'label' => 'Region',
            'required' => true,
            'options' => $regions,
        ])
        @include('components.form.select', [
            'name' => 'source_provider_id',
            'label' => 'Deployment Source',
            'required' => true,
            'options' => $sourceProviders->reduce(function ($memo, $p) {
                $memo[$p->id] = $p->name;
                return $memo;
            }, []),
        ])
        @include('components.form.input', [
            'name' => 'repository',
            'label' => 'GitHub Repository',
            'required' => true,
        ])
        <div x-show="type === 'laravel'" class="text-sm text-gray-600 mb-4">
            Can't find the repository you're looking for? Be sure to <a href="{{ \App\Services\GitHubApp::installationUrl() }}" target="_blank">grant Rafter access to it in GitHub</a>.
        </div>
        <div class="text-right">
            @component('components.button')
            Create Project
            @endcomponent
        </div>
    </form>
@endcomponent
@endsection
