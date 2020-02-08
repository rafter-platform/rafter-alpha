@extends('layouts.app')

@section('content')
    @component('environments._content', ['project' => $project, 'environment' => $environment])
        @slot('title')
            Settings
        @endslot

        <p class="mb-4 text-gray-600">
            Lorem ipsum, dolor sit amet consectetur adipisicing elit. Voluptatum debitis voluptates suscipit nam laudantium perferendis beatae id distinctio quia numquam ipsum, eius aliquid. Nesciunt accusantium unde ullam iusto distinctio eius.
        </p>

        @component('components.card')
            @slot('title')
                Environment Variables
            @endslot

            <form action="{{ route('projects.environments.settings.store', [$project, $environment]) }}" method="POST">
                @csrf
                @include('components.form.textarea', [
                    'name' => 'environmental_variables',
                    'label' => 'Environment Variables',
                    'classes' => 'font-mono',
                    'value' => $environment->environmental_variables
                ])
                <div class="text-right">
                    @component('components.button')
                        Update Environment Variables and Deploy
                    @endcomponent
                </div>
            </form>
        @endcomponent
    @endcomponent
@endsection
