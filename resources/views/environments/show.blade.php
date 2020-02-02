@extends('layouts.app')

@section('content')
    @component('components.title')
        <div class="flex items-center justify-between pb-4 border-b-2">
            <div>
                <h1 class="mb-2">
                    <a href="{{ route('projects.show', [$project]) }}">{{ $project->name }}</a>
                    / {{ $environment->name }}
                </h1>
                <a class="text-gray-600" href="{{ $environment->url }}">{{ $environment->url }}</a>
            </div>
        </div>
    @endcomponent

    @include('components.subtitle', ['title' => 'Deployments'])

    <p class="mb-4 text-gray-600">Lorem ipsum dolor sit amet consectetur adipisicing elit. Possimus facere dignissimos nam. Labore qui magni accusamus veniam deleniti cupiditate est fugiat maxime, voluptate laborum sapiente at aliquam mollitia harum aspernatur?</p>

    @foreach ($environment->deployments as $deployment)
        @component('components.item', [
            'link' => route('projects.environments.deployments.show', [$project, $environment, $deployment]),
            'list' => true
        ])
            @slot('title')
                <div class="flex justify-start">
                    <span class="mr-4">Commit Message</span>
                    @include('components.status', ['status' => $deployment->status])
                </div>
            @endslot
            @slot('meta')
                Deployed to <b>{{ $environment->name }}</b> by <b>First Last</b>
            @endslot
            @slot('status')
                {{ $deployment->created_at->diffForHumans() }}
            @endslot
        @endcomponent
    @endforeach
@endsection
