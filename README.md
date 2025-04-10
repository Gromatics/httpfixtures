# Laravel HTTP Fixture Package

This package helps you create mock HTTP responses for Laravel tests. It combines Laravel's `Http::fake` functionality with Faker data generation to create realistic test data in a format similar to Laravel factories.

### Using in Tests
Implement the fixture in your Laravel tests like this:

```
$fixture = new ExampleHttpFixture()

Http::preventStrayRequests();
Http::fake([
    "https://www.example.com/get-user/harry" => Http::response($fixture->toJson(), 200),
]);
```

This setup will:
1. Create a new fixture instance
2. Prevent any unexpected HTTP requests
3. Return your mock response when the specified URL is called

The package helps you create consistent, realistic test data while maintaining control over specific values you need to test against.




## Create a fixture

There are two ways to create a fixture:
1. Using the Artisan command:

```
php artisan make:http-fixture
```

1. Manually creating a file in the `/test/Fixtures` directory

When using the Artisan command, you can provide a real JSON response as a template. The system will ask if you want to generate faker data based on this JSON structure.

```
namespace Gromatics\HttpFixtures;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ExampleHttpFixture extends HttpFixture
{
    public function definition(): array
    {
        return [
            'status' => Arr::random(['OK', "NOK"]),
            'message' => $this->faker->sentence(),
            'items' => [
                [
                    'identifier' => Str::random(20),
                    'name' => $this->faker->company(),
                    'address' => $this->faker->address(),
                    'postcode' => $this->faker->postcode(),
                    'city' => $this->faker->city(),
                    'country' => $this->faker->country(),
                    'phone' => $this->faker->phoneNumber(),
                    'email' => $this->faker->email(),
                ]
            ]
        ];
    }
}
```

## Using Fixtures

### Basic Usage
Create a fixture with specific data:

```
$fixture = (new ExampleHttpFixture(['message' => 'Everything went ok']))->toJson();
```

This will generate a JSON response with:

```
{
    "status": "OK",
    "message": "Everything went ok",
    "items": [
        {
            "identifier": "hR8PdiRPccG0Db3B9lfg",
            "name": "Turner Group",
            "address": "352 Pacocha Harbor Apt. 285\nDorisport, SC 39336",
            "postcode": "59499-6927",
            "city": "Schillerland",
            "country": "Bouvet Island (Bouvetoya)",
            "phone": "+1 (219) 512-9679",
            "email": "willow88@friesen.biz"
        }
    ]
}
```

### Setting Nested Values
You can set values in nested objects using dot notation:

```
 $fixture = (new ExampleHttpFixture(['items.0.name' => 'John Doe']))->toJson();
```
This will return something similar to this

```
{
    "status": "OK",
    "message": "Saepe necessitatibus quidem dicta.",
    "items": [
        {
            "identifier": "1GwWuEdPQGxzZ5xcvObB",
            "name": "John Doe",
            "address": "97723 Grimes Corners\nLuismouth, WA 17117-6038",
            "postcode": "53096-7056",
            "city": "Boyermouth",
            "country": "Lesotho",
            "phone": "1-910-865-7012",
            "email": "merritt69@gmail.com"
        }
}
```









