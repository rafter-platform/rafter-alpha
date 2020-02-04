<?php

namespace App\Http\Controllers;

use App\DatabaseInstance;
use App\GoogleProject;
use Illuminate\Http\Request;

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
        //
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
