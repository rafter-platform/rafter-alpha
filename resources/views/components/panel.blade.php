@props(['instructions' => '', 'actions' => '', 'title'])

<div class="bg-white shadow sm:rounded-lg mb-8">
    <div class="px-4 py-5 sm:p-6">
        @if ($title ?? false)<h3 class="text-lg leading-6 font-medium text-gray-900">{{ $title }}</h3>@endif
        <div class="mt-2 text-sm leading-5 text-gray-600 mb-8 prose">
            {{ $instructions }}
        </div>
        {{ $slot }}
        <div class="text-right">
            {{ $actions }}
        </div>
    </div>
</div>
