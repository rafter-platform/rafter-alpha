@extends('layouts.app')

@section('content')
    @include('environments._header')
    @include('components.subtitle', ['title' => 'Deployment Details'])

    @livewire('deployment-status', $deployment)
@endsection
