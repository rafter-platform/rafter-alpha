<a href="{{ $link }}" class="p-4 px-6 block shadow-md bg-white {{ ($list ?? false) ? 'border-b border-gray-300' : 'rounded' }} flex justify-between items-center text-gray-700 hover:bg-gray-100 {{ ($list ?? false) ? 'mb-0' : 'mb-2' }}">
    <div>
        <div class="mb-1">{{ $title }}</div>
        <div class="text-gray-600 text-sm">{{ $meta ?? '' }}</div>
    </div>
    <div class="text-sm mr-0 ml-auto text-gray-600">
        {{ $status ?? '' }}
    </div>
    <div class="flex-grow-0 m-0 ml-4">
        &rarr;
    </div>
</a>
