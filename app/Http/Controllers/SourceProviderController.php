<?php

namespace App\Http\Controllers;

use App\Services\GitHubApp;
use App\SourceProvider;
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

        return redirect()->route('source-providers.edit', [$source])->with('status', 'Rafter has been installed to a new set of GitHub projects. Please choose a unique name for this installation below.');
    }

    public function edit(Request $request, SourceProvider $sourceProvider)
    {
        return view('source-providers.edit', [
            'source' => $sourceProvider,
            'repos' => $sourceProvider->client()->getRepositories()['repositories'],
        ]);
    }

    public function update(Request $request, SourceProvider $sourceProvider)
    {
        $this->validate($request, [
            'name' => ['string', 'required'],
        ]);

        $sourceProvider->update([
            'name' => $request->name,
        ]);

        return redirect()->route('projects.create')->with('status', 'GitHub installation has been updated.');
    }
}
