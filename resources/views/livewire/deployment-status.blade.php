<div wire:poll>
    <div class="bg-white">
        <div class="p-4 border-b">
            <div class="flex items-center mb-1">
                <h1 class="text-xl mr-4">{{ $deployment->commit_message }}</h1>
                @include('components.status', ['status' => $deployment->status])
            </div>
            <p class="text-sm text-gray-600">
                Deployed to <b>{{ $deployment->environment->name }}</b> by <b>{{ $deployment->initiator->name }}</b>
            </p>
        </div>
        @foreach ($deployment->steps as $step)
            <div class="flex justify-between items-center p-2 border-b">
                {{-- LOL, how is this not easier? --}}
                <span class="text-gray-700 text-sm">{{ str_replace('-', ' ', Str::title(Str::kebab($step->name))) }}</span>

                <div class="flex items-center">
                    <span class="text-sm mr-2 text-gray-600">{{ $step->duration() }}</span>
                    @include('components.status', ['status' => $step->status])
                </div>
            </div>
        @endforeach
    </div>
</div>
