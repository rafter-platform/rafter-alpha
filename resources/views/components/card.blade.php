<div {{ $attributes->merge(['class' => "bg-white overflow-hidden shadow rounded-lg" ]) }}>
    <div class="border-b border-gray-200 px-4 py-5 sm:px-6">
        <div class="-ml-4 -mt-2 flex items-center justify-between flex-wrap sm:flex-no-wrap">
            <div class="ml-4 mt-2">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    {{ $title }}
                </h3>
            </div>
            @if ($action ?? false)
                <div class="ml-4 mt-2 flex-shrink-0">
                    {{ $action }}
                </div>
            @endif
        </div>
    </div>
    <div class="px-4 py-5 sm:p-6">
        {{ $slot }}
    </div>
</div>
