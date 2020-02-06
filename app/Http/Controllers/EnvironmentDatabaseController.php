<?php

namespace App\Http\Controllers;

use App\Database;
use App\DatabaseInstance;
use App\Environment;
use App\Project;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EnvironmentDatabaseController extends Controller
{
    public function show(Project $project, Environment $environment)
    {
        return view('environments.database', [
            'project' => $project,
            'environment' => $environment,
            'databaseInstances' => $this->databaseInstances(),
            'databases' => $this->databases(),
        ]);
    }

    public function update(Request $request, Project $project, Environment $environment)
    {
        $this->validate($request, [
            'method' => ['string', 'required', 'in:new,existing'],
            'database_instance_id' => [
                'required_if:method,new',
                Rule::in($this->databaseInstances()->map->id),
            ],
            'database_id' => [
                'required_if:method,existing',
                Rule::in($this->databases()->map->id),
            ]
        ]);

        if ($request->method === 'new') {
            $environment->createDatabase(DatabaseInstance::find($request->database_instance_id));
        } else {
            $environment->database()->associate(Database::find($request->database_id));
            $environment->save();
        }

        $status = $request->method === 'new' ? 'being created' : 'connected';

        return redirect()->route('projects.environments.database', [$project, $environment])
            ->with('status', "Database $status");
    }

    /**
     * Disconnect a database from an environment.
     *
     * @param Request $request
     * @param Project $project
     * @param Environment $environment
     * @return Response
     */
    public function destroy(Request $request, Project $project, Environment $environment)
    {
        $environment->database()->dissociate();
        $environment->save();

        return redirect()->back()->with('status', 'Database disconnected');
    }

    protected function databaseInstances()
    {
        return auth()->user()->currentTeam->databaseInstances;
    }

    protected function databases()
    {
        return $this->databaseInstances()->map->databases->flatten();
    }
}
