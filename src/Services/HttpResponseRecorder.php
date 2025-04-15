<?php

namespace Gromatics\HttpFixtures\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class HttpResponseRecorder
{
    public static function recordedToHttpFixture(?string $newFixtureName = null): void
    {
        Http::recorded(function ($request, $response) use ($newFixtureName) {
            if (is_array($response->json())) {

                if (!$newFixtureName) {
                    $host = parse_url($request->url(), PHP_URL_HOST);
                    $parts = explode('.', $host);
                    $domain = $parts[count($parts) - 2];
                    $path = parse_url($request->url(), PHP_URL_PATH);
                    $lastParam = strtolower(Str::slug(preg_replace('/[^a-zA-Z0-9]/', '', basename($path))));
                    $newFixtureName = ucfirst($domain) . ucfirst($lastParam) . 'Fixture';
                }

                FileModificationService::copyExampleHttpFixture($newFixtureName, json_encode($response->json()), true);

                fwrite(STDOUT, "{$newFixtureName} created in /tests/Fixtures");

            } else {
                fwrite(STDOUT, "\033[31m" . 'Could not create HTTP Fixture. Request URL: ' . $request->url() . " returns invalid JSON. \033[0m\n");
            }
        });
    }
}
