@extends('layouts.app')

@section('content')
    <div class="mb-8 flex items-center justify-between border-b border-b-2 pb-4">
        @component('components.title', ['margin' => 'mb-0'])
            <h1>{{ $project->name }}</h1>
        @endcomponent

        <span class="text-sm uppercase text-gray-600">
            {{ $project->googleProject->name }} - {{ $project->region }}
        </span>
    </div>

    @if (session('status'))
    <div class="text-sm border border-t-8 rounded text-green-700 border-green-600 bg-green-100 px-3 py-4 mb-4" role="alert">
        {{ session('status') }}
    </div>
    @endif

    <h2 class="font-light text-2xl mb-4">Environments</h2>

    <p class="mb-4 text-gray-600">Lorem ipsum dolor sit amet consectetur adipisicing elit. Soluta neque suscipit delectus voluptates vitae laboriosam quisquam fuga deleniti, quam voluptatem quibusdam tempora ipsum doloremque sunt, dolore ut alias ea temporibus.</p>
    @component('components.card')
        @foreach ($project->environments as $environment)
            <div>
                <a href="{{ route('projects.environments.show', [$project, $environment]) }}">{{ $environment->name }}</a>
            </div>
        @endforeach
    @endcomponent
@endsection
