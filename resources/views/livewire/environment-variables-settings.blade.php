<x-settings-form x-data="{ show: false }">
    <x-slot name="title">Environment Variables</x-slot>
    <x-slot name="description">
        <p>Add key/value environmental variables to your web and worker services.</p>
        <p>Need to store sensitive secrets? Use the <a href="https://console.cloud.google.com/security/secret-manager">Secret Manager</a> and consume them using a client library in your app.</p>
        <p>Changes will take effect during the next deploy.</p>
    </x-slot>
    <x-textarea
        x-show="show"
        name="environmental_variables"
        label="Environment Variables"
        classes="font-mono"
        :value="$variables"
    />
    <p>
        <x-button x-show="!show" @click.prevent="show = true">
            <x-heroicon-o-eye class="mr-1 w-4 h-4 text-current inline-block" />
            Show environmental variables
        </x-button>
        <x-button x-show="show" @click.prevent="show = false">
            <x-heroicon-o-eye-off class="mr-1 w-4 h-4 text-current inline-block" />
            Hide environmental variables
        </x-button>
    </p>
</x-settings-form>
