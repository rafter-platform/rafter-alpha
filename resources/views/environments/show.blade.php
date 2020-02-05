@extends('layouts.app')

@section('content')
    @component('environments._content', ['project' => $project, 'environment' => $environment])
        @slot('title')
            Deployments
        @endslot

        <p class="mb-4 text-gray-600">Every time you push code, Rafter will automatically deploy a revision of your application to this environment. You can optionally disable auto-deploy or change the branch that is deployed under Settings.</p>

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
    @endcomponent
@endsection
