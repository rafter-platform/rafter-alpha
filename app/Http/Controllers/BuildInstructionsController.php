<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BuildInstructionsController extends Controller
{
    public function show(Request $request, $type, $file)
    {
        return view("build.$type.$file");
    }
}
