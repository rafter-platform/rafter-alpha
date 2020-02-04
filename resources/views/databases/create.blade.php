@extends('layouts.app')

@section('content')
@component('components.title')
    <h2>Create a Database Instance</h2>
@endcomponent

@component('components.card')

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

    @component('components.form.input', [
        'name' => 'name',
        'label' => 'Database Instance Name',
        'required' => true,
    ])
        @slot('helper')
            Name may only contain lowercase letters and hyphens.
        @endslot
    @endcomponent

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
