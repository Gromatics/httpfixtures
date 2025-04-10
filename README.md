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
### 1. Using the Artisan command:

```
php artisan make:http-fixture
```

The command will guide you through the process:

1.1 First, it asks for a fixture name:
```
 What is the class name of the HTTP Fixture? (e.g., StripeFixture, GoogleFixture):
 > StripeFixture
```
1.2 Then you can choose to create a fixture from JSON - a powerful feature of this package:
```
Want to paste a JSON response and turn it into a fixture? (yes/no) [no]:
 > y
```

> ⚠️ WARNING: MAKE SURE YOU JSON OBJECT IS MINIFIED! ⚠️

1.3. Paste your JSON object (example using Stripe response):

```
{"id": "pi_3NLxg2L1ZLzhUeQv0EXAMPLE", "object": "payment_intent", "amount": 2000, "amount_capturable": 0, "amount_received": 2000, "currency": "usd", "customer": "cus_Nv1EXAMPLE1", "description": "T-shirt order", "status": "succeeded", "payment_method": "pm_1NLxhHL1ZLzhUeQv1EXAMPLE", "receipt_email": "jenny.rosen@example.com", "created": 1712700000, "charges": {"object": "list", "data": [{"id": "ch_3NLxg2L1ZLzhUeQv0lEXAMPLE", "object": "charge", "amount": 2000, "currency": "usd", "status": "succeeded", "payment_method_details": {"card": {"brand": "visa", "last4": "4242", "exp_month": 12, "exp_year": 2026}, "type": "card"}, "receipt_url": "https://pay.stripe.com/receipts/acct_1EXAMPLE/rcpt_EXAMPLE"}], "has_more": false, "total_count": 1, "url": "/v1/charges?payment_intent=pi_3NLxg2L1ZLzhUeQv0EXAMPLE"}}
```

1.4 Choose whether to use Faker for generating values:

```
 Use faker in your Fixture? (yes/no) [no]:
 > y
```

The command will generate a fixture class like this:

```
namespace Tests\Fixtures;

use Gromatics\HttpFixtures\HttpFixture;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class StripeFixture extends HttpFixture
{

    public function definition(): array
    {
        return [
            'id' => $this->faker->sentence(5),
            'object' => $this->faker->sentence(2),
            'amount' => $this->faker->numberBetween(1, 1000),
            'amount_capturable' => $this->faker->numberBetween(1, 1000),
            'amount_received' => $this->faker->numberBetween(1, 1000),
            'currency' => $this->faker->sentence(1),
            'customer' => $this->faker->sentence(3),
            'description' => $this->faker->sentence(2),
            'status' => $this->faker->sentence(1),
            'payment_method' => $this->faker->sentence(4),
            'receipt_email' => $this->faker->email,
            'created' => $this->faker->numberBetween(1, 1000),
            'charges' => [
                'object' => $this->faker->sentence(1),
                'data' => [
                    0 => [
                        'id' => $this->faker->sentence(5),
                        'object' => $this->faker->sentence(1),
                        'amount' => $this->faker->numberBetween(1, 1000),
                        'currency' => $this->faker->sentence(1),
                        'status' => $this->faker->sentence(1),
                        'payment_method_details' => [
                            'card' => [
                                'brand' => $this->faker->sentence(1),
                                'last4' => $this->faker->numberBetween(1, 1000),
                                'exp_month' => $this->faker->numberBetween(1, 1000),
                                'exp_year' => $this->faker->numberBetween(1, 1000),
                            ],
                            'type' => $this->faker->sentence(1),
                        ],
                        'receipt_url' => $this->faker->url,
                    ],
                ],
                'has_more' => $this->faker->word(),
                'total_count' => $this->faker->numberBetween(1, 1000),
                'url' => $this->faker->sentence(9),
            ],
        ];
    }
}

```
You can modify this Fixture according to your preferences.


### 2. Manually creating a file in the `/test/Fixtures` directory

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









