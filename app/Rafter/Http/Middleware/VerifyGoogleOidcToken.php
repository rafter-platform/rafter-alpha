<?php

namespace App\Rafter\Http\Middleware;

use Closure;
use Google_Client;

class VerifyGoogleOidcToken
{
    /**
     * Verify that a request is coming from Google and contains a valid JWT token
     * matching the current Service Account email in GOOGLE_APPLICATION_CREDENTIALS.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (empty($request->bearerToken())) {
            return response("No bearer token provided", 401);
        }

        $client = new Google_Client();
        if ($client->verifyIdToken($request->bearerToken())) {
            return response("Unable to verify Google OIDC token", 401);
        }

        return $next($request);
    }
}
