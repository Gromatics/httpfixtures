<?php

namespace Gromatics\HttpFixtures\Console;

use Gromatics\HttpFixtures\Services\FileModificationService;
use Illuminate\Console\Command;
use Faker\Generator;
use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Support\Str;

class MakeHttpFixture extends Command
{

    protected $signature = 'make:http-fixture';
    protected $description = 'Create a new http fixture class';

    public function handle()
    {

        $className = $this->ask('What is the class name of the HTTP Fixture? (e.g., StripeFixture, GoogleFixture)');
        if (empty($className)) {
            $className = 'ExampleFixture';
        }

        $convertJson = $this->confirm('Want to paste a JSON response and turn it into a fixture?', false);
        if (!$convertJson) {
            $path = FileModificationService::copyExampleHttpFixture($className);
            return $this->response($path, $className);
        }

        $this->warn('⚠️ WARNING: MAKE SURE YOU JSON OBJECT IS MINIFIED! ⚠️');
        $jsonObject = $this->ask('Paste your JSON response object here.');

        // Validate that it's valid JSON
        try {
            $decodedJson = json_decode($jsonObject, true, 512, JSON_THROW_ON_ERROR);
            $this->info('✓ Valid JSON received');
        } catch (\JsonException $e) {
            $this->error('Invalid JSON provided: ' . $e->getMessage());
            return 1;
        }

        $useFaker = $this->confirm('Use faker in your Fixture?', false);
        if (!$useFaker) {
            $path = FileModificationService::copyExampleHttpFixture($className, $jsonObject);
            return $this->response($path, $className);
        }

        $path = FileModificationService::copyExampleHttpFixture($className, $jsonObject, true);
        return $this->response($path, $className);
    }

    private function response(string $path, string $className)
    {
        $this->info("🚀 Fixture created successfully at: {$path}");
        $this->info("You can use this fixture in your test like this:");
        $this->info('Http::fake(["https://www.example.com/get-user/harry" => Http::response((new ' . $className . '())->toJson())])');
        return 0;
    }


}
