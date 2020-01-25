@extends('layouts.app')

@section('content')
@component('components.card')
    @slot('title')
        Projects
    @endslot

    @foreach ($projects as $project)
        <div>
            <a href="{{ route('projects.show', [$project]) }}">{{ $project->name }}</a>
        </div>
    @endforeach

    <div class="mt-4">
        <a href="{{ route('projects.create') }}">Create new project</a>
    </div>
@endcomponent
@endsection
