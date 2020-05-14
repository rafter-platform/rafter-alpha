<x-layout>
    <x-environment :project="$project" :environment="$environment">
        <x-slot name="title">Domains</x-slot>

        @if ($environment->url)
            <x-description>
                By default, Cloud Run has assigned your service a URL of:
            </x-description>

            <ul class="mb-4 text-sm">
                <li><a class="underline text-blue-600" href="{{ $environment->url }}"><code>{{ $environment->url }}</code></a></li>
            </ul>
        @endif

        <x-description>
            You can assign one or more custom domains to your service. Add your custom domain name below, and follow the instructions.
        </x-description>

        <livewire:environment-domains :environment="$environment" />
    </x-environment>
</x-layout>
