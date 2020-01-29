<?php

namespace App\Http\Controllers;

use App\GoogleProject;
use App\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('projects.index', [
            'projects' => Auth::user()->currentTeam->projects,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('projects.create', [
            'googleProjects' => Auth::user()->currentTeam->googleProjects,
            'regions' => GoogleProject::REGIONS,
            'types' => Project::TYPES,
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
            'name' => ['string', 'required'],
            'google_project_id' => [
                'required',
                Rule::in($request->user()->currentTeam->googleProjects()->pluck('id'))
            ],
            'region' => [
                'required',
                Rule::in(collect(GoogleProject::REGIONS)->keys()),
            ],
            'type' => [
                'required',
                Rule::in(collect(Project::TYPES)->keys()),
            ],
        ]);

        $project = $request->user()->currentTeam->projects()->create([
            'name' => $request->name,
            'region' => $request->region,
            'google_project_id' => $request->google_project_id,
            'type' => $request->type,
        ]);

        $project->createInitialEnvironments();

        return redirect()->route('projects.show', [$project])->with('status', 'Project is being created');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function show(Project $project)
    {
        return view('projects.show', ['project' => $project->loadMissing('environments')]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function edit(Project $project)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Project $project)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function destroy(Project $project)
    {
        //
    }
}
