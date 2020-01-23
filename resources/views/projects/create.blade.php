@extends('layouts.app')

@section('content')
<div class="flex items-center">
    <div class="md:w-1/2 md:mx-auto">
        <h1 class="text-xl font-bold">Create a Project</h1>

        <form action="{{ route('projects.store') }}" method="POST">
            @csrf
            <div>
                <label for="name">Project Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div>
                <label for="google_project_id">Google Project</label>
                <select name="google_project_id" id="google_project_id" required>
                    @foreach ($googleProjects as $googleProject)
                    <option value="{{ $googleProject->id }}">{{ $googleProject->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="region">Region</label>
                <select name="region" id="region" required>
                    @foreach ($regions as $region => $regionName)
                    <option value="{{ $region }}">{{ $regionName }} ({{ $region }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit">Create Project</button>
            </div>
        </form>
    </div>
</div>
@endsection
