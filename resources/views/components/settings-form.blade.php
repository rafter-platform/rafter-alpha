<div class="md:grid md:grid-cols-3 md:gap-6 mb-10 sm:mb-0">
    <div class="md:col-span-1">
        <div class="px-4 sm:px-0">
            <h3 class="text-lg font-medium leading-6 text-gray-900">{{ $title }}</h3>
            <div class="mt-1 text-sm leading-5 text-gray-600 prose">
                {{ $description ?? '' }}
            </div>
        </div>
    </div>
    <div class="mt-5 md:mt-0 md:col-span-2">
        <form wire:submit.prevent="handle" method="POST" class="shadow sm:rounded-md sm:overflow-hidden">
            <div class="px-4 py-5 bg-white sm:p-6">
                {{ $slot }}
            </div>
            <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                <x-button design="primary">Save</x-button>
            </div>
        </form>
    </div>
</div>
