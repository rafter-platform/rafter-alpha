@extends('layouts.app')

@section('content')

@component('components.title')
    <h1>Databases</h1>
@endcomponent

<p class="text-gray-800 mb-4">
    Lorem ipsum, dolor sit amet consectetur adipisicing elit. Magnam, dignissimos blanditiis reprehenderit repellendus ad ex eligendi tenetur explicabo laborum veritatis quo a omnis ut facilis asperiores corporis natus nobis cumque.
</p>

<div class="mb-6">
    @foreach ($databaseInstances as $instance)
        @component('components.item', ['link' => route('database-instances.show', [$instance])])
            @slot('title')
                <div class="flex justify-start">
                    <span class="mr-4">{{ $instance->name }}</span>
                    @include('components.status', ['status' => $instance->status])
                </div>
            @endslot
            @slot('meta')
                {{ $instance->region }} / {{ $instance->tier }} / {{ $instance->size }}GB / {{ $instance->databases()->count() }} databases
            @endslot
        @endcomponent
    @endforeach
</div>

@component('components.button', ['link' => route('database-instances.create')])
Create Database
@endcomponent

@endsection
