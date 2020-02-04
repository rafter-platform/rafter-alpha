<?php

namespace App\Http\Controllers;

use App\DatabaseInstance;
use App\GoogleProject;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DatabaseInstanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('databases.index', [
            'databaseInstances' => auth()->user()->currentTeam->databaseInstances,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('databases.create', [
            'googleProjects' => auth()->user()->currentTeam->googleProjects,
            'types' => DatabaseInstance::TYPES,
            'versions' => DatabaseInstance::VERSIONS,
            'tiers' => DatabaseInstance::TIERS,
            'regions' => GoogleProject::REGIONS,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'google_project_id' => [
                'required',
                Rule::in(auth()->user()->currentTeam->googleProjects()->pluck('id'))
            ],
            'name' => ['required', 'string'],
            'type' => ['required', Rule::in(array_keys(DatabaseInstance::TYPES))],
            'version' => ['required', Rule::in(array_keys(DatabaseInstance::VERSIONS))],
            'tier' => ['required', Rule::in(array_keys(DatabaseInstance::TIERS['mysql']))],
            'size' => ['required', 'integer', 'min:10'],
            'region' => ['required', Rule::in(array_keys(GoogleProject::REGIONS))],
        ]);

        try {
            $instance = auth()->user()->currentTeam->databaseInstances()->create([
                'google_project_id' => $request->google_project_id,
                'name' => $request->name,
                'google_project_id' => $request->google_project_id,
                'type' => $request->type,
                'version' => $request->version,
                'tier' => $request->tier,
                'size' => $request->size,
                'region' => $request->region,
            ]);

            $instance->provision();

            return redirect()->route('databases.show', [$instance])->with('status', 'Database is being created.');
        } catch (Exception $e) {
            return back()->with('status', $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\DatabaseInstance  $databaseInstance
     * @return \Illuminate\Http\Response
     */
    public function show(DatabaseInstance $databaseInstance)
    {
        return view('databases.show', [
            'instance' => $databaseInstance->loadMissing('databases'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\DatabaseInstance  $databaseInstance
     * @return \Illuminate\Http\Response
     */
    public function edit(DatabaseInstance $databaseInstance)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\DatabaseInstance  $databaseInstance
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DatabaseInstance $databaseInstance)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\DatabaseInstance  $databaseInstance
     * @return \Illuminate\Http\Response
     */
    public function destroy(DatabaseInstance $databaseInstance)
    {
        //
    }
}
