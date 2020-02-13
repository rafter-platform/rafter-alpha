@extends('layouts.app')

@section('content')

@include('components.flash')

@component('components.card')
@slot('title')
    <h1>Edit GitHub Installation</h1>

    <form action="{{ route('source-providers.update', [$source]) }}" method="POST">
        @csrf
        @method('PUT')

        @component('components.form.input', [
            'name' => 'name',
            'label' => 'Installation Name',
            'required' => true,
            'value' => $source->name,
        ])
            @slot('helper')
                <p>Be sure to give your installation a unique name, e.g. <b>Acme Co. GitHub</b> or <b>Personal GitHub</b>.</p>
            @endslot
        @endcomponent
        @include('components.form.input', [
            'name' => 'installation_id',
            'label' => 'Installation ID',
            'disabled' => true,
            'value' => $source->installation_id
        ])
        @include('components.form.textarea', [
            'name' => 'repos',
            'label' => 'Available Repositories',
            'disabled' => true,
            'value' => collect($repos)
                ->map(function ($repo) {
                    return $repo['full_name'];
                })
                ->join('\n'),
        ])
        <div class="text-right">
            @component('components.button')
                Update
            @endcomponent
        </div>
    </form>
@endslot

@endcomponent

@endsection
