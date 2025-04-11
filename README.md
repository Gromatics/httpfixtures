# Laravel HTTP Fixture Package

This package helps you create mock HTTP responses for Laravel tests. It combines Laravel's `Http::fake` functionality with Faker data generation to create realistic test data in a format similar to Laravel factories.

# Using in Tests

Implement the fixture in your Laravel tests like this:

```php
Http::preventStrayRequests();

$fixture = new ExampleHttpFixture();
Http::fake([
    "https://www.example.com/get-user/harry" => Http::response($fixture->toJson(), 200),
]);
```

This setup will:

1. Prevent any unexpected HTTP requests.
2. Create a new fixture instance.
3. Return your mock response when the specified URL is called.

The package helps you create consistent, realistic test data while maintaining control over specific values you need to test against.

# Creating a Fixture

### 1. Using the Artisan Command

Run the following command:

```php
php artisan make:http-fixture
```

```
 What is the class name of the HTTP Fixture? (e.g., StripeFixture, GoogleFixture):
 > SuperApiResponseFixture
```

Choose whether to create a fixture from JSON. Learn how to create a fixture from a JSON response [here](#use-json-object-to-create-fixture).

```plaintext
Want to paste a JSON response and turn it into a fixture? (yes/no) [no]: 
 > N
```

Now you have a fixture in tests/Fixtures called `SuperApiResponseFixture.php`.


### 2. Manually Creating a File

You can manually create a fixture file in the `/test/Fixtures` directory. Customize your `definition()` method as needed, for example:

```php
namespace Gromatics\HttpFixtures;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ExampleHttpFixture extends HttpFixture
{
    public function definition(): array
    {
        return [
            'status' => Arr::random(['OK', 'NOK']),
            'message' => $this->faker->sentence,
            'items' => [
                [
                    'identifier' => Str::random(20),
                    'name' => $this->faker->company,
                    'address' => $this->faker->address,
                    'postcode' => $this->faker->postcode,
                    'city' => $this->faker->city,
                    'country' => $this->faker->country,
                    'phone' => $this->faker->phoneNumber,
                    'email' => $this->faker->email,
                ]
            ],
        ];
    }
}
```

# Using Fixtures

### Basic Usage

You can use your HTTP fixture in your tests like this:

```php
Http::fake([
    "https://www.example.com/get-user/harry" => Http::response(
    (new ExampleHttpFixture())->toJson(), 
    200),
]);
```

You can override specific keys when initializing the fixture:

```php
Http::fake([
    "https://www.example.com/get-user/harry" => Http::response(
    (new ExampleHttpFixture(['message' => 'Everything went ok']))->toJson(), 
    200),
]);
```

This will return a JSON response similar to:

```json
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

You can use dot notation to update nested values:

```php
$fixture = (new ExampleHttpFixture(['items.0.name' => 'John Doe']))->toJson();
```
This will produce a response where the first item's name is set to "John Doe".

### XML
You can also generate an XML output like this:

```php
Http::fake([
    "https://www.example.com/get-user/harry" => Http::response(
    (new ExampleHttpFixture())->toXml('yourRootElement'), 
    200),
]);
```

This will return a XML response similar to:

```xml
<?xml version="1.0"?>\n
<yourRootElement>
	<status>OK</status>
	<message>Voluptatum fugit aspernatur non.</message>
	<items>
		<item>
			<identifier>6zzzWFhmyRyPZwoYa6b8</identifier>
			<name>Schiller, Gislason and Reynolds</name>
			<address>5708 Lockman Gardens Armstronghaven, FL 32188</address>
			<postcode>87862-9490</postcode>
			<city>North Sabrina</city>
			<country>Romania</country>
			<phone>(346) 871-2661</phone>
			<email>marion66@yahoo.com</email>
		</item>
	</items>
</yourRootElement>
```


# Use JSON object to create fixture
The command will guide you through the process:

Run the following command:

```php
php artisan make:http-fixture
```

Enter a fixture name:

```plaintext
 What is the class name of the HTTP Fixture? (e.g., StripeFixture, GoogleFixture): 
 > StripeFixture
```

Choose whether to create a fixture from JSON:

```plaintext
Want to paste a JSON response and turn it into a fixture? (yes/no) [no]: 
 > y
```

> ⚠️ **WARNING:** Ensure that your JSON object is minified when pasting it into the console! ⚠️

Paste your minified JSON object (example below uses a Stripe response):

```plaintext
Paste your JSON response object here.:
> {"id: "pi_3NL....
```

```json
{
    "id": "pi_3NLxg2L1ZLzhUeQv0EXAMPLE",
    "object": "payment_intent",
    "amount": 2000,
    "amount_capturable": 0,
    "amount_received": 2000,
    "currency": "usd",
    "customer": "cus_Nv1EXAMPLE1",
    "description": "T-shirt order",
    "status": "succeeded",
    "payment_method": "pm_1NLxhHL1ZLzhUeQv1EXAMPLE",
    "receipt_email": "jenny.rosen@example.com",
    "created": 1712700000,
    "charges": {
        "object": "list",
        "data": [
            {
                "id": "ch_3NLxg2L1ZLzhUeQv0lEXAMPLE",
                "object": "charge",
                "amount": 2000,
                "currency": "usd",
                "status": "succeeded",
                "payment_method_details": {
                    "card": {
                        "brand": "visa",
                        "last4": "4242",
                        "exp_month": 12,
                        "exp_year": 2026
                    },
                    "type": "card"
                },
                "receipt_url": "https://pay.stripe.com/receipts/acct_1EXAMPLE/rcpt_EXAMPLE"
            }
        ],
        "has_more": false,
        "total_count": 1,
        "url": "/v1/charges?payment_intent=pi_3NLxg2L1ZLzhUeQv0EXAMPLE"
    }
}
```

Decide whether to use Faker for generating values:

```plaintext
 Use faker in your Fixture? (yes/no) [no]: 
 > y
```

The command will generate a fixture class similar to this:

```php
namespace Tests\Fixtures;

use Gromatics\HttpFixtures\HttpFixture;
use Illuminate\Support\Str;

class StripeFixture extends HttpFixture
{
    public function definition(): array
    {
        return [
            'id' => Str::random(20),
            'object' => $this->faker->word(),
            'amount' => $this->faker->numberBetween(100, 10000),
            'amount_capturable' => $this->faker->numberBetween(100, 10000),
            'amount_received' => $this->faker->numberBetween(100, 10000),
            'currency' => $this->faker->currencyCode(),
            'customer' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'status' => 'succeeded',
            'payment_method' => $this->faker->word(),
            'receipt_email' => $this->faker->email(),
            'created' => $this->faker->numberBetween(10, 10000),
            'charges' => [
                'object' => $this->faker->word(),
                'data' => [
                    0 => [
                        'id' => Str::random(20),
                        'object' => $this->faker->word(),
                        'amount' => $this->faker->numberBetween(100, 10000),
                        'currency' => $this->faker->currencyCode(),
                        'status' => 'succeeded',
                        'payment_method_details' => [
                            'card' => [
                                'brand' => $this->faker->word(),
                                'last4' => $this->faker->numberBetween(10, 10000),
                                'exp_month' => $this->faker->numberBetween(10, 10000),
                                'exp_year' => $this->faker->year(),
                            ],
                            'type' => $this->faker->word(),
                        ],
                        'receipt_url' => $this->faker->url(),
                    ],
                ],
                'has_more' => $this->faker->boolean(),
                'total_count' => $this->faker->numberBetween(10, 10000),
                'url' => $this->faker->url(),
            ],
        ];
    }
}
```


