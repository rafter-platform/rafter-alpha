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
    <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script>
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
    @livewireStyles
</head>
<body class="bg-gray-50">
    <div>
        <x-global-nav />

        @if ($title ?? false)
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <div class="lg:flex lg:items-center lg:justify-between">
                    <div class="flex-1 min-w-0">
                        <h1 class="text-2xl sm:text-3xl leading-7 sm:leading-9 sm:truncate font-bold leading-tight text-gray-900">
                            {{ $title }}
                        </h1>
                        @if ($meta ?? false)
                        <div class="mt-1 flex flex-col sm:mt-0 sm:flex-row sm:flex-wrap">
                            {{ $meta }}
                        </div>
                        @endif
                    </div>
                    @if ($actions ?? false)
                        <div class="mt-5 flex lg:mt-0 lg:ml-4">
                            {{ $actions }}
                        </div>
                    @endif
                </div>
            </div>
        </header>
        @endif
        <main>
            <div class="max-w-7xl mx-auto py-6 px-6 lg:px-8">
                <x-flash />

                {{ $slot }}
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="{{ mix('js/app.js') }}"></script>
    @livewireScripts
</body>
</html>
