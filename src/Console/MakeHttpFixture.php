<?php

namespace Gromatics\HttpFixtures\Console;

use Illuminate\Console\Command;
use Faker\Generator;

class MakeHttpFixture extends Command
{

    protected $signature = 'make:http-fixture';
    protected $description = 'Create a new http fixture class';

    public function handle()
    {

        $name = $this->ask('What is the name of the HTTP Fixture? (e.g., StripeFixture, GoogleFixture)');
        if (empty($name)) {
            $this->error('The name is required.');
            return;
        }

        $this->info("You have entered: $name");

        $this->info('👋 Hello from Gromatics HttpFixtures!');
    }

}
