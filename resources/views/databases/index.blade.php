@extends('layouts.app')

@section('content')
@component('components.card')
    @slot('title')
        <h1>Databases</h1>
    @endslot

    @unless ($databaseInstances->count())
        <p>You haven't created any database instances. Create one below.</p>
    @endunless

    @foreach ($databaseInstances as $instance)
        <p>{{ $instance->name }}</p>
    @endforeach
@endcomponent

@component('components.card')
    @slot('title')
        <h2>Create a Database Instance</h2>
    @endslot

    <form action="{{ route('databases.store') }}" method="POST">
        @csrf

        @include('components.form.select', [
            'name' => 'google_project_id',
            'label' => 'Google Project',
            'required' => true,
            'options' => $googleProjects->reduce(function ($memo, $p) {
                $memo[$p->id] = $p->name;
                return $memo;
            }, [])
        ])

        @include('components.form.input', [
            'name' => 'name',
            'label' => 'Database Instance Name',
            'required' => true,
        ])

        @include('components.form.select', [
            'name' => 'type',
            'label' => 'Database Type',
            'required' => true,
            'options' => $types,
        ])

        @include('components.form.select', [
            'name' => 'version',
            'label' => 'Database Version',
            'required' => true,
            'options' => $versions,
        ])

        @include('components.form.select', [
            'name' => 'tier',
            'label' => 'Database Instance Tier',
            'required' => true,
            'options' => $tiers['mysql'],
        ])

        @include('components.form.input', [
            'name' => 'size',
            'label' => 'Database Disk Size (GB)',
            'required' => true,
            'value' => 10,
            'min' => 10,
            'type' => 'number'
        ])

        @include('components.form.select', [
            'name' => 'region',
            'label' => 'Region',
            'required' => true,
            'options' => $regions,
        ])

        <div class="text-right">
            @component('components.button')
                Create Database Instance
            @endcomponent
        </div>
    </form>
@endcomponent
@endsection
