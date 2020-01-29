<?php

namespace App\Http\Controllers;

use App\GoogleProject;
use App\Project;
use App\Rules\ValidRepository;
use App\SourceProvider;
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
            'sourceProviders' => Auth::user()->sourceProviders,
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
            'source_provider_id' => [
                'required',
                Rule::exists('source_providers', 'id')->where(function ($query) {
                    $query->where('user_id', $this->user()->id);
                })
            ],
            'region' => [
                'required',
                Rule::in(collect(GoogleProject::REGIONS)->keys()),
            ],
            'type' => [
                'required',
                Rule::in(collect(Project::TYPES)->keys()),
            ],
            'repository' => [
                'required',
                'string',
                new ValidRepository(SourceProvider::find($this->source_provider_id))
            ],
        ]);

        $project = $request->user()->currentTeam->projects()->create([
            'name' => $request->name,
            'region' => $request->region,
            'google_project_id' => $request->google_project_id,
            'type' => $request->type,
            'source_provider_id' => $request->source_provider_id,
            'repository' => $request->repository,
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
