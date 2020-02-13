@extends('layouts.app')

@section('content')
    @component('components.title')
        <h1>Source Providers</h1>
    @endcomponent

    <p class="mb-4 text-gray-600">
        Source Providers like GitHub allow Rafter to connect to your code and deploy it to the cloud. View existing source provider
        installations below, and create new installations by clicking the button.
    </p>

    @foreach ($sources as $source)
        @component('components.item', ['link' => route('source-providers.edit', [$source])])
            @slot('title')
                {{ $source->name }}
            @endslot
            @slot('status')
                Created {{ $source->created_at->diffForHumans() }}
            @endslot
        @endcomponent
    @endforeach

    <div class="mt-8">
        <a href="{{ \App\Services\GitHubApp::installationUrl() }}" class="button">Create New GitHub Installation</a>
    </div>
@endsection
