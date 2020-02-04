@extends('layouts.app')

@section('content')

@component('components.title')
    Databases / {{ $instance->name }}
@endcomponent

@component('components.card')
    @slot('title')
        Databases in {{ $instance->name }}
    @endslot

    <ul>
        @foreach ($instance->databases as $database)
            <li>{{ $database->name }}</li>
        @endforeach
    </ul>
@endcomponent

@endsection
