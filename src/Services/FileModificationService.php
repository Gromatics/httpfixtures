<?php

namespace Gromatics\HttpFixtures\Services;

use Illuminate\Support\Str;

class FileModificationService
{
    /**
     * @return string
     *
     * @throws \Exception
     */
    public static function copyExampleHttpFixture(string $className, ?string $json = null, bool $useFaker = false)
    {

        $exampleFixturePath = dirname(__FILE__, 2).'/ExampleHttpFixture.php';
        $fixtureDestinationPath = base_path("tests/Fixtures/{$className}.php");

        if (! file_exists($exampleFixturePath)) {
            throw new \Exception('The ExampleHttpFixture.php file was not found.');
        }

        if (! is_dir(dirname($fixtureDestinationPath))) {
            mkdir(dirname($fixtureDestinationPath), 0755, true);
        }

        // Read the content of the example fixture
        $content = file_get_contents($exampleFixturePath);
        if ($content === false) {
            throw new \Exception('Failed to read the example fixture file.');
        }

        $content = static::replaceContentClass($content, $className, $json, $useFaker);

        // Write the modified content to the new file
        if (file_put_contents($fixtureDestinationPath, $content) !== false) {
            return $fixtureDestinationPath;
        } else {
            throw new \Exception('Failed to create the fixture.');
        }
    }

    /**
     * @return array|string|string[]
     *
     * @throws \Exception
     */
    private static function replaceContentClass(string $content, string $className, ?string $json = null, bool $useFaker = false)
    {

        if ($json) {
            $content = static::parseJson($content, $json, $useFaker);
        }

        // Replace the namespace
        $content = str_replace(
            'namespace Gromatics\HttpFixtures;',
            "namespace Tests\Fixtures;\n\nuse Gromatics\HttpFixtures\HttpFixture;",
            $content
        );

        $content = str_replace(
            'class ExampleHttpFixture',
            "class {$className}",
            $content
        );

        return $content;
    }

    /**
     * @return array|string|string[]|null
     *
     * @throws \Exception
     */
    private static function parseJson(string $content, string $json, bool $useFaker = false)
    {
        try {
            $arr = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \Exception('Invalid JSON provided: '.$e->getMessage());
        }

        if (! $useFaker) {
            // Remove everything between  return [ and ] and replace with array;
            return preg_replace('/return \[\s*(.*?)\s*\];/s', 'return '.self::exportArray($arr).';', $content);
        }

        return preg_replace('/return \[\s*(.*?)\s*\];/s', 'return '.self::exportArrayWithFaker($arr).';', $content);
    }

    // Replace array() with []
    private static function exportArray($arr)
    {
        $export = var_export($arr, true);
        $patterns = [
            "/array \(/i" => '[',
            "/^([ ]*)\)(,?)$/m" => '$1]$2',
            "/=>[ ]?\n[ ]+\[/m" => '=> [',
            "/\)(\n[ ]*\])/m" => ']$1',
            '/^([ ]{2,})/m' => '        $1', // Add proper indentation
        ];

        return preg_replace(array_keys($patterns), array_values($patterns), $export);
    }

    private static function replaceArrayWithFakerTypes(array $arr): array
    {
        $result = [];
        foreach ($arr as $key => $value) {
            if (is_array($value)) {
                $result[$key] = self::replaceArrayWithFakerTypes($value);
            } else {
                $result[$key] = self::determineTypeFaker($key, $value);
            }
        }

        return $result;
    }

    private static function determineTypeFaker(mixed $key, mixed $value): string
    {
        $key = strtolower($key);
        switch (true) {
            case $key === 'id':
                return is_string($value) && ! is_numeric($value) ? 'Str::random(20)' : '$this->faker->numberBetween(100000, 999999)';
            case $key === 'identifier':
                return 'Str::random(20)';
            case str_contains($key, 'firstname'):
            case str_contains($key, 'first_name'):
                return '$this->faker->firstName()';
            case str_contains($key, 'lastname'):
            case str_contains($key, 'last_name'):
                return '$this->faker->lastName()';
            case str_contains($key, 'name'):
                return '$this->faker->name()';
            case str_contains($key, 'company'):
            case str_contains($key, 'organisation'):
            case str_contains($key, 'business'):
                return '$this->faker->company()';
            case str_contains($key, 'address'):
                return '$this->faker->address()';
            case str_contains($key, 'zipcode'):
            case str_contains($key, 'postcode'):
            case str_contains($key, 'postalcode'):
            case str_contains($key, 'postal_code'):
            case $key === 'zip':
                return '$this->faker->postcode()';
            case str_contains($key, 'city'):
            case str_contains($key, 'locality'):
                return '$this->faker->city()';
            case str_contains($key, 'state'):
                return '$this->faker->state()';
            case str_contains($key, 'country'):
                return '$this->faker->countryCode()';
            case str_contains($key, 'email'):
                return '$this->faker->email()';
            case str_contains($key, 'phone'):
                return '$this->faker->phoneNumber()';
            case str_contains($key, 'url'):
            case str_contains($key, 'href'):
            case str_contains($key, 'link'):
                return '$this->faker->url()';
            case str_contains($key, 'description'):
                return '$this->faker->sentence()';
            case str_contains($key, 'title'):
                return '$this->faker->words(3, true)';
            case str_contains($key, 'currency'):
                return '$this->faker->currencyCode()';
            case str_contains($key, 'year'):
                return '$this->faker->year()';
            case str_contains($key, 'status'):
                return $value;
            case is_bool($value):
                return '$this->faker->boolean()';
            case static::isValidTimestamp($value):
                return '$this->faker->unixTime()';
            case is_int($value):
                $floor = 0;
                $ceil = 0;
                if ($value > 0) {
                    // Calculate magnitude (rounding to the nearest 10, 100, etc.)
                    $magnitude = pow(10, floor(log10($value)));

                    // Ensure that the floor is rounded to the next magnitude that starts with 1 (e.g., 2000 to 1000)
                    if ($value < ($magnitude * 10)) {
                        $floor = $magnitude;  // Round to the nearest magnitude that starts with 1 (e.g., 2000 to 1000)
                    } else {
                        $floor = floor($value / $magnitude) * $magnitude;
                    }

                    // Get number of digits in the value
                    $numDigits = floor(log10($value)) + 1;

                    // Calculate the base starting with 9
                    $base = (int) str_pad('9', $numDigits, '9');

                    // Set the ceiling value to the next valid number starting with 9
                    if ($value <= $base) {
                        $ceil = $base;
                    } else {
                        $ceil = (int) str_pad('9', $numDigits + 1, '9');
                    }
                }

                return '$this->faker->numberBetween('.$floor.', '.$ceil.')';
            case is_numeric($value):
                return '$this->faker->numberBetween(10, 10000)';
            case is_string($value) && str_contains($value, ' '):
                $wordCount = substr_count($value, ' ') + 1;

                return '$this->faker->sentence('.$wordCount.')';
            case $value === null:
                return 'null';
            default:
                return '$this->faker->word()';
        }
    }

    private static function exportArrayWithFaker(array $arr): string
    {
        $arr = self::replaceArrayWithFakerTypes($arr);

        // Convert array to string representation
        $export = var_export($arr, true);

        // Replace array syntax and clean up the format
        $patterns = [
            "/array \(/i" => '[',
            "/^([ ]*)\)(,?)$/m" => '$1]$2',
            "/=>[ ]?\n[ ]+\[/m" => '=> [',
            "/\)(\n[ ]*\])/m" => ']$1',
            '/^([ ]{2,})/m' => '        $1', // Add proper indentation
            // Remove quotes around faker calls
            "/['\"]\\\$this->faker->(.*?)['\"]/" => '$this->faker->$1',
            // Remove quotes around Str::random calls
            "/['\"]Str::random\((.*?)\)['\"]/" => 'Str::random($1)',
            // Remove quotes around null values
            "/['\"]null['\"]/" => 'null',
        ];

        return preg_replace(array_keys($patterns), array_values($patterns), $export);
    }

    private static function isValidTimestamp($timestamp)
    {
        // Check if the timestamp is a valid integer
        if (! is_int($timestamp)) {
            return false;
        }

        // Define the valid range for the timestamp
        $start = strtotime('1995-01-01 00:00:00');
        $end = strtotime('2050-12-31 23:59:59');

        // Check if the timestamp is within the range
        return $timestamp >= $start && $timestamp <= $end;
    }
}
