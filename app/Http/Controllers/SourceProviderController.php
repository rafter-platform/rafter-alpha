<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SourceProviderController extends Controller
{
    public function store(Request $request)
    {
        $code = $request->code;
        $installationId = $request->installation_id;

        $source = $request->user()->sourceProviders()->create([
            'name' => 'GitHub',
            'type' => 'GitHub',
            'installation_id' => $installationId,
            'meta' => [],
        ]);

        // Exchange the code for an access token for the user, and store it
        $response = $source->client()->exchangeCodeForAccessToken($code);

        if (empty($response['access_token'])) {
            throw ValidationException::withMessages([
                'github' => 'Missing access token',
            ]);
        }

        $meta = ['token' => $response['access_token']];
        $source->update(['meta' => $meta]);

        if (! $source->client()->valid()) {
            $source->delete();

            throw ValidationException::withMessages([
                'meta' => ['The given credentials are invalid.'],
            ]);
        }

        return redirect()->route('projects.create')->with('status', 'GitHub has been connected');
    }
}
