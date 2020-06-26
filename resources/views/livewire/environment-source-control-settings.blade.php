<x-settings-form title="Source Control">
    <x-slot name="description">Settings related to your Git source control provider for this environment.</x-slot>

    <x-input label="Branch" wire:model="branch" name="branch">
        <x-slot name="helper">Your environment will be automatically deployed whenever you push or merge to this branch.
        </x-slot>
    </x-input>
</x-settings-form>
