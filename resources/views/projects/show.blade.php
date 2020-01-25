@extends('layouts.app')

@section('content')
@component('components.card')
    @slot('title')
        <h1>{{ $project->name }}</h1>
    @endslot

    @if (session('status'))
        <div class="text-sm border border-t-8 rounded text-green-700 border-green-600 bg-green-100 px-3 py-4 mb-4" role="alert">
            {{ session('status') }}
        </div>
    @endif

    @foreach ($project->environments as $environment)
        <div>
            <a href="{{ route('projects.environments.show', [$project, $environment]) }}">{{ $environment->name }}</a>
        </div>
    @endforeach
@endcomponent
@endsection
