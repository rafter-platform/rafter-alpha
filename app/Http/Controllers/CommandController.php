<?php

namespace App\Http\Controllers;

use App\Command;
use App\Environment;
use App\Project;
use Illuminate\Http\Request;

class CommandController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Project $project, Environment $environment)
    {
        return view('environments.commands.index', [
            'project' => $project,
            'environment' => $environment,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Command  $command
     * @return \Illuminate\Http\Response
     */
    public function show(Project $project, Environment $environment, Command $command)
    {
        return view('environments.commands.show', [
            'project' => $project,
            'environment' => $environment,
            'command' => $command,
        ]);
    }
}
