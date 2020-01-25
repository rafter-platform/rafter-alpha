@extends('layouts.app')

@section('content')
    @if (session('status'))
        <div class="text-sm border border-t-8 rounded text-green-700 border-green-600 bg-green-100 px-3 py-4 mb-4" role="alert">
            {{ session('status') }}
        </div>
    @endif

    @component('components.card')
        @slot('title')
            {{ $user->currentTeam->name }} Dashboard
        @endslot

        <p>Hi!</p>
    @endcomponent
@endsection
