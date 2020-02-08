<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v1.9.8/dist/alpine.js" defer></script>
    @livewireStyles
</head>
<body class="bg-gray-200 h-screen antialiased leading-normal">
    <div id="app">
        <div class="flex">
            <div class="min-h-screen flex flex-col">
                <div class="bg-blue-900 p-4">
                    <a href="{{ url('/home') }}" class="text-lg font-semibold text-gray-100 no-underline">
                        {{ config('app.name', 'Laravel') }}
                    </a>
                </div>
                <div class="p-4 bg-blue-800 text-blue-100 w-64 flex-1">
                    <div class="mb-8">
                        <a href="{{ route('home') }}">Dashboard</a>
                    </div>

                    <div class="text-sm text-blue-200 uppercase font-bold tracking-widest">Projects</div>
                    <ul class="mt-4 mb-8">
                        @foreach (Auth::user()->currentTeam->projects as $project)
                            <li>
                                <a href="{{ route('projects.show', [$project]) }}">{{ $project->name }}</a>
                            </li>
                        @endforeach
                    </ul>

                    <div class="text-sm text-blue-200 uppercase font-bold tracking-widest">Manage</div>
                    <ul class="mt-4 mb-8">
                        <li class="mb-2"><a href="{{ route('database-instances.index') }}">Databases</a></li>
                        <li><a href="{{ route('google-projects.index') }}">Google Projects</a></li>
                    </ul>
                </div>
            </div>
            <div class="w-full">
                <div class="text-right p-4 bg-white">
                    <span class="text-sm pr-4">{{ Auth::user()->name }}</span>

                    <a href="{{ route('logout') }}"
                        class="no-underline hover:underline text-sm p-3"
                        onclick="event.preventDefault();
                            document.getElementById('logout-form').submit();">{{ __('Logout') }}</a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                        {{ csrf_field() }}
                    </form>
                </div>
                <div class="flex items-center" style="max-width: 1000px">
                    <div class="p-8 w-full">
                        @yield('content')
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ mix('js/app.js') }}"></script>
    @livewireScripts
</body>
</html>
