<?php

namespace App\Http\Livewire;

use App\DatabaseInstance;
use App\GoogleProject;
use App\RandomName;
use App\Rules\ValidDatabaseInstanceName;
use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Validation\Rule;
use Livewire\Component;

class DatabaseInstanceForm extends Component
{
    public $type = 'mysql';
    public $version;
    public $tier;
    public $size;
    public $name;
    public $databaseGoogleProjectId;
    public $databaseRegion;

    public function mount($googleProjectId = '', $region = '')
    {
        $instance = new DatabaseInstance;

        $this->version = $instance->getOption('version');
        $this->tier = $instance->getOption('tier');
        $this->name = RandomName::withToken();
        $this->databaseGoogleProjectId = $googleProjectId;
        $this->databaseRegion = $region;
    }

    public function updated($field)
    {
        $this->validateOnly($field, [
            'name' => [
                'required',
                'max:78',
                'regex:/^[a-z][a-z\d\-]*$/',
                new ValidDatabaseInstanceName(GoogleProject::find($this->databaseGoogleProjectId))
            ],
        ], [
            'name.regex' => 'Use lowercase letters, numbers, or hyphens. Start with a letter.',
        ]);
    }

    public function render()
    {
        return view('livewire.database-instance-form', [
            'versions' => DatabaseInstance::VERSIONS,
            'tiers' => DatabaseInstance::TIERS['mysql'],
            'projects' => currentTeam()->googleProjects,
            'regions' => GoogleProject::REGIONS,
        ]);
    }

    public function create()
    {
        $this->validate([
            'databaseGoogleProjectId' => [
                'required',
                Rule::in(currentTeam()->googleProjects()->pluck('id'))
            ],
            'databaseRegion' => [
                'required',
                Rule::in(array_keys(GoogleProject::REGIONS)),
            ],
            'name' => [
                'required',
                'max:78',
                new ValidDatabaseInstanceName(GoogleProject::find($this->databaseGoogleProjectId))
            ],
            'version' => [
                'required',
                Rule::in(array_keys(DatabaseInstance::VERSIONS)),
            ],
            'type' => [
                'required',
                Rule::in(DatabaseInstance::TYPES),
            ],
            'version' => [
                'required',
                Rule::in(array_keys(DatabaseInstance::VERSIONS)),
            ],
            'tier' => [
                'required',
                Rule::in(array_keys(DatabaseInstance::TIERS['mysql'])),
            ],
        ]);

        $instance = currentTeam()->databaseInstances()->create([
            'name' => $this->name,
            'google_project_id' => $this->databaseGoogleProjectId,
            'type' => $this->type,
            'options' => [
                'version' => $this->version,
                'tier' => $this->tier,
                'region' => $this->databaseRegion,
            ],
        ]);

        try {
            $instance->provision();

            $this->emit('databaseInstanceCreated', $instance->id);
        } catch (RequestException $e) {
            if ($e->getCode() == 409) {
                $this->addError('name', 'This database name is already in use or was used too recently.');
            }

            $this->addError('databaseInstance', $e->response['error']['message'] ?? $e->getMessage());

            $instance->delete();

            return;
        }
    }
}
