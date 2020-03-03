<?php

namespace App\Http\Controllers;

use App\GoogleProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GoogleProjectController extends Controller
{
    public function __construct() {
        $this->authorizeResource('App\GoogleProject');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('google-projects.index', ['googleProjects' => Auth::user()->currentTeam->googleProjects]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
            'project_id' => ['string', 'required'],
            'service_account_json' => ['json', 'required'],
        ]);

        $googleProject = $request->user()->currentTeam->googleProjects()->create([
            'name' => $request->name,
            'project_id' => $request->project_id,
            'service_account_json' => json_decode($request->service_account_json),
        ]);

        $googleProject->provision();

        return redirect('/google-projects')->with('status', 'Project added and is being activated for use by Rafter.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\GoogleProject  $googleProject
     * @return \Illuminate\Http\Response
     */
    public function show(GoogleProject $googleProject)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\GoogleProject  $googleProject
     * @return \Illuminate\Http\Response
     */
    public function edit(GoogleProject $googleProject)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\GoogleProject  $googleProject
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, GoogleProject $googleProject)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\GoogleProject  $googleProject
     * @return \Illuminate\Http\Response
     */
    public function destroy(GoogleProject $googleProject)
    {
        //
    }
}
