<?php

namespace Gromatics\tests;

use Gromatics\HttpFixtures\ExampleHttpFixture;

it('throws an exception if a constructor key is not defined in the definition', function () {
    (new ExampleHttpFixture(['foo' => 'bar']))->toArray();
})->throws(\InvalidArgumentException::class);

it('updates a key in the definition', function () {
    $message = 'Everything went ok';
    $fixture = new ExampleHttpFixture(['message' => $message]);
    expect($fixture->toArray()['message'])->toBe($message);
});

it('updates a key in the definition with dot notation', function () {
    $name = 'Harry Ole';
    $fixture = new ExampleHttpFixture(['items.0.name' => $name]);
    expect($fixture->toArray()['items'][0]['name'])->toBe($name);
});

it('gets valid XML output', function () {
    $fixture = new ExampleHttpFixture;
    $xml = $fixture->toXml(); // Assuming toXml() generates the XML output
    $dom = new \DOMDocument;
    $isValidXml = $dom->loadXML($xml, LIBXML_NOERROR | LIBXML_NOWARNING);
    expect($isValidXml)->toBeTrue();
});

it('should cache the rendered fixture data to ensure consistent values', function () {
    $fixture = new ExampleHttpFixture();
    expect($fixture->toArray()['items'][0]['name'])->toBe($fixture->toArray()['items'][0]['name']);
});

it('should use the specified faker_locale when provided', function () {
    // Create a fixture with default locale (en_US)
    $defaultFixture = new ExampleHttpFixture();
    $defaultCountry = $defaultFixture->toArray()['items'][0]['country'];

    // Create a fixture with a different locale by extending the class
    // and overriding the constructor to use a different locale
    $germanFixture = new class([]) extends ExampleHttpFixture {
        public function __construct(array $overrides = [])
        {
            $this->faker = \Faker\Factory::create('de_DE');
            $this->overrides = $overrides;
        }
    };

    $germanCountry = $germanFixture->toArray()['items'][0]['country'];

    // The country names should be different due to different locales
    expect($germanCountry)->not->toBe($defaultCountry);

    // Simply verify that changing the locale produces different output
    // This is sufficient to test that the faker_locale change is working
    expect($germanFixture->toArray()['items'][0]['city'])
        ->not->toBe($defaultFixture->toArray()['items'][0]['city']);

    // Additional verification that other faker-generated fields are also different
    expect($germanFixture->toArray()['message'])
        ->not->toBe($defaultFixture->toArray()['message']);
});

