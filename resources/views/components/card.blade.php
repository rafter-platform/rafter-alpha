<div {{ $attributes->merge(['class' => "card break-words bg-white border border-2 rounded shadow mb-4 p-4"]) }}">
    @unless (empty($title))
    <div class="text-xl mb-4">
        {{ $title }}
    </div>
    @endunless

    <div class="text-gray-700 font-normal">
        {{ $slot }}
    </div>
</div>
