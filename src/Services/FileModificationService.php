<?php


namespace Gromatics\HttpFixtures\Services;

class FileModificationService
{

    public static function copyExampleHttpFixture(string $className, ?string $json = null, bool $useFaker = false)
    {

        $exampleFixturePath = dirname(__FILE__, 2) . '/ExampleHttpFixture.php';
        $fixtureDestinationPath = base_path("tests/Fixtures/{$className}.php");

        if (!file_exists($exampleFixturePath)) {
            throw new \Exception('The ExampleHttpFixture.php file was not found.');
        }

        if (!is_dir(dirname($fixtureDestinationPath))) {
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


    private static function parseJson(string $content, string $json, bool $useFaker = false)
    {
        try {
            $arr = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \Exception('Invalid JSON provided: ' . $e->getMessage());
        }

        if(!$useFaker) {
            //Remove everything between  return [ and ] and replace with array;
            return  preg_replace('/return \[\s*(.*?)\s*\];/s', 'return ' . self::exportArray($arr) . ';', $content);
        }
        return preg_replace('/return \[\s*(.*?)\s*\];/s', 'return ' . self::exportArrayWithFaker($arr) . ';', $content);
    }

    //Replace array() with []
    private static function exportArray($arr) {
        $export = var_export($arr, true);
        $patterns = [
            "/array \(/i" => '[',
            "/^([ ]*)\)(,?)$/m" => '$1]$2',
            "/=>[ ]?\n[ ]+\[/m" => '=> [',
            "/\)(\n[ ]*\])/m" => ']$1'
        ];
        return preg_replace(array_keys($patterns), array_values($patterns), $export);
    }

    private static function replaceArrayWithFakerTypes(array $arr): array {
        $result = [];
        foreach ($arr as $key => $value) {
            if (is_array($value)) {
                $result[$key] = self::replaceArrayWithFakerTypes($value);
            } else {
                $result[$key] = self::determineTypeFaker($value);
            }
        }
        return $result;
    }

    private static function determineTypeFaker(mixed $value): string {
        return match(true) {
            is_numeric($value) => '$this->faker->numberBetween(1, 1000)',
            filter_var($value, FILTER_VALIDATE_URL) !== false => '$this->faker->url',
            is_string($value) && preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $value) => '$this->faker->hexColor',
            is_string($value) && preg_match('/^[A-Z]{3}$/', $value) => '$this->faker->currencyCode',
            is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL) !== false => '$this->faker->email',
            is_string($value) && preg_match('/^[a-z]{2}_[A-Z]{2}$/', $value) => '$this->faker->locale',
            is_string($value) && str_word_count($value) > 0 => '$this->faker->sentence(' . str_word_count($value) . ')',
            default => '$this->faker->word()'
        };
    }

    private static function exportArrayWithFaker(array $arr): string {
        $arr = self::replaceArrayWithFakerTypes($arr);

        // Convert array to string representation
        $export = var_export($arr, true);

        // Replace array syntax and clean up the format
        $patterns = [
            "/array \(/i" => '[',
            "/^([ ]*)\)(,?)$/m" => '$1]$2',
            "/=>[ ]?\n[ ]+\[/m" => '=> [',
            "/\)(\n[ ]*\])/m" => ']$1',
            // Remove quotes around faker calls
            "/['\"]\\\$this->faker->(.*?)['\"]/" => '$this->faker->$1',
        ];

        return preg_replace(array_keys($patterns), array_values($patterns), $export);
    }



}
