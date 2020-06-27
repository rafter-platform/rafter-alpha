<x-layout>
    <x-slot name="title">
        <div class="flex justify-between items-center">
            <div>{{ auth()->user()->currentTeam->name }} Dashboard</div>
            <div>
                <x-button href="/projects/create">New Project</x-button>
            </div>
        </div>
    </x-slot>

    <x-table>
        <x-slot name="thead">
            <x-th>
                Project
            </x-th>
            <x-th>
                Source
            </x-th>
            <x-th>
                Google Cloud Project
            </x-th>
            <x-th>
                Last Deployed
            </x-th>
            <x-th last />
        </x-slot>
        @foreach (currentTeam()->projects as $project)
            <x-tr>
                <x-td>
                    <a href="/projects/{{ $project->id }}">
                        <x-project-logo :type="$project->type" class="w-5 h-5 mr-2 inline-block" />
                        {{ $project->name }}
                    </a>
                </x-td>
                <x-td>
                    @if ($project->sourceProvider)
                        <x-source-provider-logo :type="$project->sourceProvider->type" class="w-5 h-5 mr-2 inline-block" />
                        {{ $project->repository }}
                    @else
                        <x-source-provider-logo type="cli" class="w-5 h-5 mr-2 inline-block" />
                        CLI
                    @endif
                </x-td>
                <x-td>
                    <x-icon-google-cloud class="w-5 h-5 mr-2 inline-block" />
                    {{ $project->googleProject->project_id }}
                </x-td>
                <x-td>
                    @if ($project->environments()->first()->activeDeployment)
                        {{ $project->environments()->first()->activeDeployment->created_at->diffForHumans() }}
                    @endif
                </x-td>
                <x-td last>
                    <div class="flex items-center justify-end">
                        <x-text-link class="mr-3" :href="route('projects.show', [$project])">View</x-text-link>
                        <x-dropdown-menu>
                            <x-dropdown-menu-item :href="$project->productionUrl()" target="_blank">
                                <x-slot name="icon">
                                    <x-heroicon-o-external-link class="w-5 h-5 inline-block" />
                                </x-slot>
                                Open Production
                            </x-dropdown-menu-item>
                        </x-dropdown-menu>
                    </div>
                </x-td>
            </x-tr>
        @endforeach
    </x-table>
</x-layout>
