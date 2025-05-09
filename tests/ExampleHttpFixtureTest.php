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
