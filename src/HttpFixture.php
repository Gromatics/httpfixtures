<?php

namespace Gromatics\HttpFixtures;

use Faker\Factory;
use Faker\Generator;
use Illuminate\Container\Container;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class HttpFixture
{

    protected Generator $faker;
    protected array $overrides;

    /**
     * @param array $overrides
     */
    public function __construct(array $overrides = [])
    {
        $this->faker = Factory::create();
        $this->overrides = $overrides;
    }

    /**
     * @return array
     */
    protected function get(): array
    {
        foreach ($this->overrides as $key => $value) {
            if (!Arr::has($this->definition(), $key)) {
                throw new \InvalidArgumentException("The key '{$key}' is not defined in the default definition.");
            }
        }

        $result = $this->definition();
        foreach ($this->overrides as $key => $value) {
            Arr::set($result, $key, $value);
        }
        return $result;
    }

    /**
     * @return array
     */
    protected function definition(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function toArray(): array {
        return $this->get();
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function toCollection():Collection {
        return collect($this->get());
    }

    /**
     * @return string
     */
    public function toJson(): string {
        return json_encode($this->get());
    }


    public function toXml() {}


}
