@extends('layouts.app')

@section('content')
    @include('environments._header')
    @include('components.subtitle', ['title' => 'Deployment Details'])

    @component('components.card')
        @slot('title')
            Deployment Steps
        @endslot

        @foreach ($deployment->steps as $step)
            <div class="flex justify-between items-center py-2 border-b">
                {{-- LOL, how is this not easier? --}}
                <span>{{ str_replace('-', ' ', Str::title(Str::kebab($step->name))) }}</span>

                <div class="flex items-center">
                    <span class="text-sm mr-2 text-gray-600">{{ $step->duration() }}</span>
                    @include('components.status', ['status' => $step->status])
                </div>
            </div>
        @endforeach
    @endcomponent
@endsection
