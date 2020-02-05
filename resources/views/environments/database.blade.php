@extends('layouts.app')

@section('content')
    @component('environments._content', ['project' => $project, 'environment' => $environment])
        @slot('title')
            Database
        @endslot

        <p class="mb-4 text-gray-600">Connect a database to this environment.</p>
    @endcomponent
@endsection
