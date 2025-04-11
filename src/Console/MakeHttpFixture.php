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

        $convertJson = $this->confirm('Want to use a JSON file and turn it into a fixture?', false);
        if (!$convertJson) {
            $path = FileModificationService::copyExampleHttpFixture($className);
            return $this->response($path, $className);
        }

        $jsonPath = $this->ask("What's the path of your JSON file? (e.g., storage/app/stripe-fixture.json");
        if (!file_exists($jsonPath)) {
            $this->error('The file does not exist at the provided path.');
            return 1;
        }

        $jsonContent = file_get_contents($jsonPath);

        if ($jsonContent === false) {
            $this->error('Failed to read the file content. Please check the file path and permissions.');
            return 1;
        }

        $jsonObject = trim($jsonContent);


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
