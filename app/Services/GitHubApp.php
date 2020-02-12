<?php

namespace App\Services;

use GuzzleHttp\Client;

class GitHubApp
{
    /**
     * Get the URL to the installation configuration page on GitHub
     *
     * @return string
     */
    public static function installationUrl(): string
    {
        return "https://github.com/apps/" . config('services.github.app_name') . "/installations/new";
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
}
