<form
    x-data="{
        showGoogleProjectForm: false
    }"
    x-init="
        window.livewire.on('googleProjectAdded', projectId => {
            showGoogleProjectForm = false;
            $refs.serviceAccountJson.value = '';
        })
    "
    wire:submit.prevent="create">
    <h2 class="text-lg font-medium mb-4">Where is your project's code?</h2>

    <x-radio-button-group name="sourceType">
        <x-radio-button wire:model="sourceType" name="sourceType" value="github">
            <x-slot name="icon">
                <x-source-provider-logo type="github" class="text-current w-6 h-6" />
            </x-slot>
            GitHub
        </x-radio-button>
        <x-radio-button wire:model="sourceType"  name="sourceType" value="gitlab" disabled>
            <x-slot name="icon">
                <x-source-provider-logo type="gitlab" class="text-current w-6 h-6" />
            </x-slot>
            Gitlab
        </x-radio-button>
        <x-radio-button wire:model="sourceType"  name="sourceType" value="bitbucket" disabled>
            <x-slot name="icon">
                <x-source-provider-logo type="bitbucket" class="text-current w-6 h-6" />
            </x-slot>
            Bitbucket
        </x-radio-button>
        <x-radio-button wire:model="sourceType" name="sourceType" value="cli" disabled>
            <x-slot name="icon">
                <x-source-provider-logo type="cli" class="text-current w-6 h-6" />
            </x-slot>
            Command Line
        </x-radio-button>
    </x-radio-button-group>

    @if ($sourceType == 'github')
        <p class="mb-4 text-gray-800">
            GitHub allows you to provide granular access to different repositories and organizations using <b>installations</b>.
            Select the installation below containing your repository, or create a new installation.
        </p>
        <x-radio-button-group name="sourceProviderId">
            @foreach ($sourceProviders->filter(fn ($p) => $p->type == 'github') as $item)
                <x-radio-button wire:model="sourceProviderId" name="sourceProviderId" value="{{ $item->id }}" small>
                    @if ($item->meta['avatar'] ?? false)
                    <x-slot name="icon">
                        <img src="{{ $item->meta['avatar'] }}" alt="Avatar" class="w-5 h-5 rounded-full">
                    </x-slot>
                    @endif
                    <div>
                        {{ $item->name }}
                        <span class="inline-flex ml-1 items-center px-2.5 py-0.5 rounded-full text-xs font-medium leading-4 bg-gray-200 text-gray-800">
                            {{ count($item->meta['repositories'] ?? []) }}
                        </span>
                    </div>
                </x-radio-button>
            @endforeach
            <x-radio-button @click.prevent="startOAuthFlow('{{ $newGitHubInstallationUrl }}', 'github')" small>
                <x-slot name="icon">
                    <x-heroicon-o-cog class="text-current w-5 h-5" />
                </x-slot>
                Add or Modify Installation
            </x-radio-button>
        </x-radio-button-group>
    @endif

    @if ($sourceType == 'gitlab')
    <x-radio-button-group>
        @if ($gitlab = $sourceProviders->firstWhere('type', 'gitlab'))
            <x-radio-button wire:model="sourceProviderId" name="sourceProviderId" value="{{ $gitlab->id }}" checked>
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
    @endif

    @if ($sourceType == 'bitbucket')
    <x-radio-button-group>
        @if ($bitbucket = $sourceProviders->firstWhere('type', 'bitbucket'))
            <x-radio-button wire:model="sourceProviderId" name="sourceProviderId" value="{{ $bitbucket->id }}" checked>
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
    @endif

    @if ($sourceProviderId && $sourceType != 'cli')
        <x-input
            wire:model="repository"
            label="Repository"
            name="repository"
            list="repos"
            placeholder="{{ $sourceType == 'github' && $this->sourceProvider->isGitHub() ? $this->sourceProvider->name : 'account' }}/repository">
            <x-slot name="helper">If you cannot find a repository, double-check that Rafter has access to it.</x-slot>
        </x-input>

        @if ($this->sourceProvider->isGitHub())
            <datalist id="repos">
                @foreach ($this->sourceProvider->meta['repositories'] as $repo)
                    <option value="{{ $repo }}" />
                @endforeach
            </datalist>
        @endif

        <x-input
            wire:model="name"
            label="Project Name"
            name="name"
            placeholder="name">
            <x-slot name="helper">This will be used when creating Cloud Run services.</x-slot>
        </x-input>

        <h2 class="text-lg font-medium mb-4 mt-12">What type of project?</h2>

        <x-radio-button-group name="type">
            @foreach (\App\Project::TYPES as $key => $label)
            <x-radio-button wire:model="type" name="type" value="{{ $key }}">
                <x-slot name="icon">
                    <x-project-logo :type="$key" class="w-6 h-6" />
                </x-slot>
                {{ $label }}
            </x-radio-button>
            @endforeach
        </x-radio-button-group>

        <x-textarea
        label="Add any initial environment variables"
        name="variables"
        wire:model="variables"
        classes="font-mono">
            <x-slot name="helper">
                @if ($type == 'rails')
                    <x-heroicon-s-exclamation class="w-5 h-5 text-current text-yellow-300 inline-block" />
                    Rails requires <code>RAILS_MASTER_KEY</code> to be set.
                @endif
            </x-slot>
        </x-textarea>

        <h2 class="text-lg font-medium mb-4 mt-12">Which Google Cloud Project?</h2>

        <x-radio-button-group name="googleProjectId">
            @foreach ($projects as $project)
            <x-radio-button wire:model="googleProjectId" name="googleProjectId" value="{{ $project->id }}">
                <x-slot name="icon">
                    <x-icon-google-cloud class="text-current w-6 h-6" />
                </x-slot>
                {{ $project->project_id }}
            </x-radio-button>
            @endforeach
            <x-radio-button @click.prevent="showGoogleProjectForm = !showGoogleProjectForm">
                <x-slot name="icon">
                    <x-heroicon-o-plus class="text-current w-6 h-6" />
                </x-slot>
                Connect A Project
            </x-radio-button>
        </x-radio-button-group>

        <div x-show="showGoogleProjectForm" class="bg-white shadow sm:rounded-lg mb-8">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Add Google Cloud Project</h3>
                <div class="mt-2 text-sm leading-5 text-gray-600 mb-8 prose">
                    <p class="mb-2">
                        To add a Google Cloud project to Rafter, start by creating a <a href="https://console.cloud.google.com/iam-admin/serviceaccounts" target="_blank" rel="noopener">service account</a> for your project.
                        Give it a name that makes sense to you, like <code>rafter</code>.
                    </p>

                    <p class="mb-2">
                        <b>Important:</b> You must give the service account the <b>Owner</b> role in order for Rafter to function properly.
                        On the final step, click <b>Create Key</b> and download a JSON-formatted key. Attach the JSON file below.
                    </p>

                    <p>
                        Note: An active billing account must be attached to your Google Cloud project.
                    </p>
                </div>
                <x-input
                    wire:model="serviceAccountJson"
                    x-ref="serviceAccountJson"
                    name="serviceAccountJson"
                    label="Attach the service account JSON file"
                    type="file"
                    accept="application/json" />
                <div class="text-right">
                    <x-button wire:click.prevent="addGoogleProject">Add Project</x-button>
                </div>
            </div>
        </div>

        @if ($googleProjectId)
            <h2 class="text-lg font-medium mb-4 mt-12">Which Google Cloud region?</h2>

            <x-radio-button-group name="region">
                @foreach ($regions as $key => $region)
                <x-radio-button wire:model="region" name="region" value="{{ $key }}" small>
                    {{ $region }} ({{ $key }})
                </x-radio-button>
                @endforeach
            </x-radio-button-group>
        @endif

        <div class="text-right">
            <x-button
                design="primary"
                size="xl"
                wire:loading.attr="disabled"
                wire:target="create">
                <span class="mr-2">🚀</span>
                Create Project
            </x-button>
        </div>
    @endif

    @if ($sourceType == 'cli')
        <p>Instructions for adding a project via CLI go here...</p>
    @endif
</form>

@push('scripts')
<script>
var windowObjectReference = null;
var previousUrl = null;
var closeInterval = null;

function startOAuthFlow(url, name) {
    window.removeEventListener('message', receiveMessage);
    if (closeInterval) clearInterval(closeInterval);

    var strWindowFeatures = 'toolbar=no, menubar=no, width=1040, height=700, top=100, left=100';

    if (windowObjectReference === null || windowObjectReference.closed) {
        windowObjectReference = window.open(url, name, strWindowFeatures);
        windowObjectReference.addEventListener('beforeunload', handleClose);
    } else if (previousUrl !== url) {
        windowObjectReference = window.open(url, name, strWindowFeatures);
        windowObjectReference.focus();
    } else {
        windowObjectReference.focus();
    }

    window.addEventListener('message', receiveMessage, false);

    closeInterval = setInterval(() => {
        if (windowObjectReference && windowObjectReference.closed) {
            handleClose();
            clearInterval(closeInterval);
        }
    }, 500);

    previousUrl = url;
};

function receiveMessage(event) {
    if (event.origin !== window.location.origin) {
        return;
    }

    const { data } = event;

    if (data.source === 'github') {
        if (closeInterval) clearInterval(closeInterval);

        const { payload } = data;

        @this.call('handleOauthCallback', payload, data.source);
    }
};

function handleClose() {
    @this.call('handleOauthClose');
}
</script>
@endpush
