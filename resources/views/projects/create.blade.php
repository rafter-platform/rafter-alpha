@extends('layouts.app')

@section('content')
@component('components.card')
    @slot('title')
        <h1>Create a Project</h1>
    @endslot

    <form action="{{ route('projects.store') }}" method="POST">
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
            'options' => $types
        ])
        @include('components.form.select', [
            'name' => 'region',
            'label' => 'Region',
            'required' => true,
            'options' => $regions,
        ])
        <div class="text-right">
            @component('components.button')
            Create Project
            @endcomponent
        </div>
    </form>
@endcomponent
@endsection
