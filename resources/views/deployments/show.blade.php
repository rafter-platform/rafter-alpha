@extends('layouts.app')

@section('content')
    @include('environments._header')
    @include('components.flash')

    <div class="flex justify-between">
        @include('components.subtitle', ['title' => 'Deployment Details'])
        <form action="{{ route('projects.environments.deployments.redeploy', [$project, $environment, $deployment]) }}" method="POST">
            @csrf
            @component('components.button')
                Redeploy
            @endcomponent
        </form>
    </div>

    @livewire('deployment-status', $deployment)
@endsection
