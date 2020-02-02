@extends('layouts.app')

@section('content')
    @include('environments._header')
    @include('components.subtitle', ['title' => 'Deployments'])

    <p class="mb-4 text-gray-600">Lorem ipsum dolor sit amet consectetur adipisicing elit. Possimus facere dignissimos nam. Labore qui magni accusamus veniam deleniti cupiditate est fugiat maxime, voluptate laborum sapiente at aliquam mollitia harum aspernatur?</p>

    @foreach ($environment->deployments as $deployment)
        @component('components.item', [
            'link' => route('projects.environments.deployments.show', [$project, $environment, $deployment]),
            'list' => true
        ])
            @slot('title')
                <div class="flex justify-start">
                    <span class="mr-4">{{ $deployment->commit_message }}</span>
                    @include('components.status', ['status' => $deployment->status])
                </div>
            @endslot
            @slot('meta')
                Deployed to <b>{{ $environment->name }}</b> by <b>{{ $deployment->initiator->name }}</b>
            @endslot
            @slot('status')
                {{ $deployment->created_at->diffForHumans() }}
            @endslot
        @endcomponent
    @endforeach
@endsection
