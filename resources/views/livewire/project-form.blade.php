<form x-data="{ sourceType: '', sourceProvider: '', googleProject: '' }">
    <h2 class="text-lg font-medium mb-4">Where is your project's code?</h2>

    <x-radio-button-group>
        <x-radio-button x-model="sourceType" name="source_type" value="github">
            <x-slot name="icon">
                <x-icon-github class="text-current w-6 h-6" />
            </x-slot>
            GitHub
        </x-radio-button>
        <x-radio-button x-model="sourceType"  name="source_type" value="gitlab">
            <x-slot name="icon">
                <x-icon-gitlab class="text-current w-6 h-6" />
            </x-slot>
            Gitlab
        </x-radio-button>
        <x-radio-button x-model="sourceType"  name="source_type" value="bitbucket">
            <x-slot name="icon">
                <x-icon-bitbucket class="text-current w-6 h-6" />
            </x-slot>
            Bitbucket
        </x-radio-button>
        <x-radio-button @click="sourceProvider = ''" x-model="sourceType"  name="source_type" value="cli">
            <x-slot name="icon">
                <x-heroicon-o-desktop-computer class="text-current w-6 h-6" />
            </x-slot>
            Command Line
        </x-radio-button>
    </x-radio-button-group>

    <x-radio-button-group x-show="sourceType == 'github'">
        @foreach ($sourceProviders->filter(fn ($p) => $p->type == 'GitHub') as $item)
        <x-radio-button x-model="sourceProvider" name="source_provider" value="{{ $item->id }}">
            {{ $item->name }}
        </x-radio-button>
        @endforeach
        <x-radio-button @click.prevent="window.alert('new gh install')">
            <x-slot name="icon">
                <x-heroicon-o-plus class="text-current w-6 h-6" />
            </x-slot>
            New Installation
        </x-radio-button>
    </x-radio-button-group>

    <x-radio-button-group x-show="sourceType == 'gitlab'">
        @if ($gitlab = $sourceProviders->firstWhere('type', 'Gitlab'))
            <x-radio-button x-model="sourceProvider" name="source_provider" value="{{ $gitlab->id }}" checked>
                <x-slot name="icon">
                    <x-heroicon-o-desktop-computer class="text-current w-6 h-6" />
                </x-slot>
                {{ $gitlab->name }}
            </x-radio-button>
        @else
            <x-radio-button @click.prevent="window.alert('new gh install')">
                <x-slot name="icon">
                    <x-heroicon-o-plus class="text-current w-6 h-6" />
                </x-slot>
                Connect Gitlab
            </x-radio-button>
        @endif
    </x-radio-button-group>

    <x-radio-button-group x-show="sourceType == 'bitbucket'">
        @if ($bitbucket = $sourceProviders->firstWhere('type', 'Bitbucket'))
            <x-radio-button x-model="sourceProvider" name="source_provider" value="{{ $bitbucket->id }}" checked>
                <x-slot name="icon">
                    <x-heroicon-o-desktop-computer class="text-current w-6 h-6" />
                </x-slot>
                {{ $bitbucket->name }}
            </x-radio-button>
        @else
            <x-radio-button @click.prevent="window.alert('new gh install')">
                <x-slot name="icon">
                    <x-heroicon-o-plus class="text-current w-6 h-6" />
                </x-slot>
                Connect Bitbucket
            </x-radio-button>
        @endif
    </x-radio-button-group>

    <div x-show="sourceProvider">
        <x-input label="Repository" name="repository" placeholder="username/repository" />

        <h2 class="text-lg font-medium mb-4">What type of project?</h2>

        <x-radio-button-group x-data="{}">
            @foreach (\App\Project::TYPES as $key => $type)
            <x-radio-button name="type" value="{{ $type }}">
                <x-slot name="icon">
                    @if ($key == 'laravel')
                        <x-icon-laravel class="w-6 h-6" />
                    @elseif ($key == 'nodejs')
                        <x-icon-nodejs class="w-6 h-6" />
                    @else
                        <x-heroicon-o-desktop-computer class="w-6 h-6 text-current" />
                    @endif
                </x-slot>
                {{ $type }}
            </x-radio-button>
            @endforeach
        </x-radio-button-group>

        <x-input label="Project Name" name="repository" placeholder="username/repository" />

        <h2 class="text-lg font-medium mb-4">Please select your Google Cloud Project, or connect a new one.</h2>

        <x-radio-button-group>
            @foreach ($projects as $project)
            <x-radio-button x-model="googleProject" name="google_project_id" value="{{ $project->id }}">
                <x-slot name="icon">
                    <x-icon-google-cloud class="text-current w-6 h-6" />
                </x-slot>
                {{ $project->project_id }}
            </x-radio-button>
            @endforeach
            <x-radio-button @click.prevent="window.alert('new google project')">
                <x-slot name="icon">
                    <x-heroicon-o-plus class="text-current w-6 h-6" />
                </x-slot>
                Connect A Project
            </x-radio-button>
        </x-radio-button-group>

        <div x-show="googleProject">
            <h2 class="text-lg font-medium mb-4">Which Google Cloud region?</h2>

            <x-radio-button-group x-data="{}">
                @foreach ($regions as $key => $region)
                <x-radio-button name="region" value="{{ $region }}">
                    {{ $region }} ({{ $key }})
                </x-radio-button>
                @endforeach
            </x-radio-button-group>
        </div>

        Database? Y/N

        Which Database instance?
    </div>

    <div x-show="sourceType == 'cli'">
        <p>Instructions for adding a project via CLI go here...</p>
    </div>
</form>
