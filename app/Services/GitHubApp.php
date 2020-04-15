<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Http\Request;

class GitHubApp
{
    /**
     * Get the URL to the installation configuration page on GitHub
     *
     * @return string
     */
    public static function installationUrl($installationId = null): string
    {
        return "https://github.com/apps/" . config('services.github.app_name') . "/installations/" . ($installationId ?: 'new');
    }

    /**
     * Exchange a code for an access token
     *
     * @param string $code
     * @return array
     */
    public static function exchangeCodeForAccessToken($code)
    {
        $response = (new Client)
            ->post(
                "https://github.com/login/oauth/access_token",
                [
                    "json" => [
                        "client_id" => config('services.github.client_id'),
                        "client_secret" => config('services.github.client_secret'),
                        "code" => $code,
                    ],
                ]
            )
            ->getBody()
            ->getContents();

        $result = [];

        // The result is returned in a query-string format:
        // access_token=xyz&other_thing=xyz
        parse_str($response, $result);

        return $result;
    }

    /**
     * Verify whether an incoming payload matches a SHA1 webhook secret signature
     *
     * @param string $payload
     * @param string $signature
     * @return boolean
     */
    public static function verifyWebhookPayload(Request $request)
    {
        $payload = $request->instance()->getContent();
        $signature = $request->header('X-Hub-Signature');
        $hash = 'sha1=' . hash_hmac('sha1', $payload, config('services.github.webhook_secret'));

        return $hash === $signature;
    }
}
