@extends('layouts.app')

@section('content')

@component('components.title')
    <div class="border-b-2">
        <div class="mb-4">
            <a href="{{ route('database-instances.index') }}">Databases</a> /
            {{ $instance->name }}
        </div>
        <div class="flex items-center mb-4">
            <div class="text-sm text-gray-800 mr-4">
                {{ $instance->region }} / {{ $instance->tier }} / {{ $instance->size }}GB
            </div>
            @include('components.status', ['status' => $instance->status])
        </div>
    </div>
@endcomponent

@include('components.flash')

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
