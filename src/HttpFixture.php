<?php

namespace Gromatics\HttpFixtures;

use Faker\Factory;
use Faker\Generator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class HttpFixture
{
    protected Generator $faker;

    protected array $overrides;

    public function __construct(array $overrides = [])
    {
        $this->faker = Factory::create();
        $this->overrides = $overrides;
    }

    protected function get(): array
    {
        foreach ($this->overrides as $key => $value) {
            if (! Arr::has($this->definition(), $key)) {
                throw new \InvalidArgumentException("The key '{$key}' is not defined in the default definition.");
            }
        }

        $result = $this->definition();
        foreach ($this->overrides as $key => $value) {
            Arr::set($result, $key, $value);
        }

        return $result;
    }

    protected function definition(): array
    {
        return [];
    }

    public function toArray(): array
    {
        return $this->get();
    }

    public function toCollection(): Collection
    {
        return collect($this->get());
    }

    public function toJson(): string
    {
        return json_encode($this->get());
    }

    /**
     * @return bool|string
     *
     * @throws \Exception
     */
    public function toXml(string $rootElement = 'root')
    {
        $xml = new \SimpleXMLElement('<'.$rootElement.'/>');
        $this->arrayToXml($this->get(), $xml);

        return $xml->asXML();
    }

    protected function arrayToXml(array $array, \SimpleXMLElement &$xml): void
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (is_numeric($key)) {
                    $key = 'item'; // Handle numeric array keys
                }
                $child = $xml->addChild($key);
                $this->arrayToXml($value, $child);
            } else {
                if (is_numeric($key)) {
                    $key = 'item'; // Handle numeric array keys
                }
                $xml->addChild($key, htmlspecialchars((string) $value));
            }
        }
    }
}
