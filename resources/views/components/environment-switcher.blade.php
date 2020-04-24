<x-dropdown-menu label="Switch Environment">
    <x-dropdown-menu-group>
        @foreach ($project->environments as $environment)
            <x-dropdown-menu-item :href="route('projects.environments.show', [$project, $environment])">{{ ucfirst($environment->name) }}</x-dropdown-menu-item>
        @endforeach
    </x-dropdown-menu-group>
    <x-dropdown-menu-divider />
    <x-dropdown-menu-item href="#">
        <x-slot name="icon">
            <x:heroicon-o-plus :class="\App\View\Components\DropdownMenu::iconClass()" />
        </x-slot>
        Create Environment
    </x-dropdown-menu-item>
</x-dropdown-menu>
