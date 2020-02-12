<?php

namespace App\Http\Controllers;

use App\Services\GitHubApp;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SourceProviderController extends Controller
{
    public function store(Request $request)
    {
        $code = $request->code;
        $installationId = $request->installation_id;

        // Exchange the code for an access token for the user, and store it
        // TODO: Extract this to a GitHub specific request or controller,
        // so we can eventually support BitBucket et al.
        $response = GitHubApp::exchangeCodeForAccessToken($code);

        if (empty($response['access_token'])) {
            throw ValidationException::withMessages([
                'github' => 'Missing access token',
            ]);
        }

        $source = $request->user()->sourceProviders()->create([
            'name' => 'GitHub',
            'type' => 'GitHub',
            'installation_id' => $installationId,
            'meta' => ['token' => $response['access_token']],
        ]);

        if (! $source->client()->valid()) {
            $source->delete();

            throw ValidationException::withMessages([
                'meta' => ['The given credentials are invalid.'],
            ]);
        }

        return redirect()->route('projects.create')->with('status', 'GitHub has been connected. Now create your first Rafter project.');
    }
}
