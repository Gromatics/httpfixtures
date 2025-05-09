<?php

namespace Gromatics\HttpFixtures\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class HttpResponseRecorder
{
    public static function recordedToHttpFixture(?string $newFixtureName = null): void
    {
        $fixtureData = [];
        Http::recorded(function ($request, $response) use (&$fixtureData, $newFixtureName) {
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
                fwrite(STDOUT, "\n\033[42m\033[30mFixture created for url: " . Str::limit($request->url(), 50) . " >  /tests/Fixtures/{$newFixtureName}\033[0m\n");
                $fixtureData[] = ['url' => $request->url(), 'fixture' => $newFixtureName];
            } else {
                fwrite(STDOUT, "\033[31m" . 'Could not create HTTP Fixture. Request URL: ' . Str::limit($request->url(), 50) . " returns invalid JSON. \033[0m\n");
            }
        });

        //Show Example in console
        if (!empty($fixtureData)) {
            fwrite(STDOUT, "\033[94m" . "Http::fake([" . "\033[0m\n");
            foreach ($fixtureData as $fData) {
                fwrite(STDOUT, "\033[94m " . "'" . strtok($fData['url'], '?') . "*' => Http::response((new \Tests\Fixtures\\" . $fData['fixture'] . "())->toJson(),  200)" . (next($fixtureData) ? ',' : '') . "\033[0m\n");
            }
            fwrite(STDOUT, "\033[94m" . "]);" . "\033[0m\n");
        }
    }
}
