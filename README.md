# Laravel HTTP Fixtures

This package helps you create mock HTTP responses for Laravel tests. It combines Laravel's `Http::fake` functionality with Faker data generation to create realistic test data in a format similar to Laravel factories.

## Contents
- [Laravel HTTP Fixtures](#laravel-http-fixtures)
    - [1. Installation](#1-installation)
    - [2. What is a HTTP fixture and why do I need it?](#2-what-is-a-http-fixture-and-why-do-i-need-it)
    - [3. Creating a Fixture](#3-creating-a-fixture)
        - [3.1 Create a fixture from a real JSON response](#31-create-a-fixture-from-a-real-json-response)
        - [3.2 Create a fixture using the Artisan command](#32-create-a-fixture-using-the-artisan-command)
        - [3.3 Create a fixture from a JSON file](#33-create-a-fixture-from-a-json-file)

    - [4. Use a HTTP fixture in tests](#4-use-a-http-fixture-in-tests)
    - [5. Fixture options](#5-fixture-options)


---

# 1. Installation

```
composer require gromatics/http-fixtures --dev
```
That's all you need to install the package as a development dependency in your Laravel project.


## 2. What is a HTTP fixture and why do I need it?

An HTTP fixture is a class that mocks the data of a JSON API endpoint. Sometimes you don't want to hit a real API in your tests. APIs can go down, and while your code may work perfectly, a downed API can cause your tests to fail. A common way to
solve this is to save the JSON response and serve it using `Http::fake`, like this:

```php
$json = file_get_contents(dirname(__FILE__) . '/../../Fixtures/response.json');
Http::fake(["https://example.com/api" => Http::response($json, 200)]);
```

A saved JSON response can contain sensitive data, and it can be cumbersome to filter all of it out before committing the JSON to your repository. The Laravel HTTP Fixture package handles this for you. It can automatically create a fixture from an
HTTP request and uses the Faker library to replace sensitive values, similar to how Laravel factories work. A HTTP fixture looks like this:

```php
namespace Tests\Fixtures;

use Gromatics\HttpFixtures\HttpFixture;
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

---

## 3. Creating a Fixture

### 3.1 Create a fixture from a real JSON response

The easiest way to create a fixture is by recording a real HTTP request in your test. You can do this by placing the Http::record() method before calling the service that makes the HTTP requests. After the service has finished, use
HttpResponseRecorder::recordedToHttpFixture() to save the responses as fixtures.

A service can make multiple requests, and for each unique request, a new fixture will be created. If the same request is made more than once, it won’t duplicate the fixture.

For example:

```php
use Gromatics\Httpfixtures\Services\HttpResponseRecorder;
use Illuminate\Support\Facades\Http;

it('creates a HTTP Fixture from a real JSON request', function () {
    Http::record(); 
    Http::get('https://api.stackexchange.com/2.2/search?order=desc&sort=activity&intitle=perl&site=stackoverflow&limit=1');
    HttpResponseRecorder::recordedToHttpFixture();
});
```

```text
You can optionally provide a custom fixture name by passing it to the recordedToHttpFixture() method:
HttpResponseRecorder::recordedToHttpFixture('CustomFixtureName');
```


This will create a StackexchangeSearchFixture.php file in /tests/Fixtures, which might look like this:

```php
class StackexchangeSearchFixture extends HttpFixture
{

    public function definition(): array
    {
        return [
          'items' => [
            0 => [
              'tags' => [
                0 => $this->faker->word(),
                1 => $this->faker->word(),
                2 => $this->faker->word(),
              ],
              'owner' => [
                'reputation' => $this->faker->numberBetween(10, 99),
                'user_id' => $this->faker->numberBetween(1000000, 9999999),
                'user_type' => $this->faker->word(),
                'profile_image' => $this->faker->word(),
                'display_name' => $this->faker->name(),
                'link' => $this->faker->url(),
              ],
              'is_answered' => $this->faker->boolean(),
              'view_count' => $this->faker->numberBetween(100, 999),
              'answer_count' => $this->faker->numberBetween(1, 9),
              'score' => $this->faker->numberBetween(0, 0),
              'last_activity_date' => $this->faker->unixTime(),
              'creation_date' => $this->faker->unixTime(),
              'last_edit_date' => $this->faker->unixTime(),
              'question_id' => $this->faker->numberBetween(10000000, 99999999),
              'content_license' => $this->faker->sentence(3),
              'link' => $this->faker->url(),
              'title' => $this->faker->words(3, true),
            ],
            ...
```

---

### 3.2 Create a fixture using the Artisan command
You can also create a fixture using an Artisan command. Just run the command below and follow the on-screen instructions:

```php
php artisan make:http-fixture
```
---

### 3.3 Create a fixture from a JSON file

You can also create a fixture from a saved JSON file. For example, if you’ve saved a Stripe API response in your storage directory, you can do the following:
```plaintext
php artisan make:http-fixture

What is the class name of the HTTP Fixture? (e.g., StripeFixture, GoogleFixture): 
> StripeFixture

Want to paste a JSON response and turn it into a fixture? (yes/no) [no]: 
> y

What's the path of your JSON file? (e.g. storage/app/stripe-fixture.json:
> storage/app/stripe-fixture.json
```

If your Stripe response looks like this;

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

Then the command will generate a fixture class similar to this:

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
            'amount' => $this->faker->numberBetween(1000, 9999),
            'amount_capturable' => $this->faker->numberBetween(0, 0),
            'amount_received' => $this->faker->numberBetween(1000, 9999),
            'currency' => $this->faker->currencyCode(),
            'customer' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'status' => 'succeeded',
            'payment_method' => $this->faker->word(),
            'receipt_email' => $this->faker->email(),
            'created' => $this->faker->numberBetween(1000000000, 9999999999),
            'charges' => [
                'object' => $this->faker->word(),
                'data' => [
                    0 => [
                        'id' => Str::random(20),
                        'object' => $this->faker->word(),
                        'amount' => $this->faker->numberBetween(1000, 9999),
                        'currency' => $this->faker->currencyCode(),
                        'status' => 'succeeded',
                        'payment_method_details' => [
                            'card' => [
                                'brand' => $this->faker->word(),
                                'last4' => $this->faker->numberBetween(10, 10000),
                                'exp_month' => $this->faker->numberBetween(10, 99),
                                'exp_year' => $this->faker->year(),
                            ],
                            'type' => $this->faker->word(),
                        ],
                        'receipt_url' => $this->faker->url(),
                    ],
                ],
                'has_more' => $this->faker->boolean(),
                'total_count' => $this->faker->numberBetween(1, 9),
                'url' => $this->faker->url(),
            ],
        ];
    }
}
```
---

## 4. Use a HTTP fixture in tests

You can use your HTTP fixture in your tests like this:

```php
Http::fake(["https://api.stripe.com/v1/*" => Http::response(
    (new StripeFixture())->toJson(),  200),
]);
```

You can override specific keys when initializing the fixture:
```php
Http::fake(["https://api.stripe.com/v1/*" => Http::response(
    (new StripeFixture(['description' => 'My first Stripe payment']))->toJson(),  200),
]);
```

This will return a JSON response similar to:

```json
{
    "id": "CuOmvFTXUcSDMqRhm7ZI",
    "object": "voluptas",
    "amount": 7026,
    "amount_capturable": 2396,
    "amount_received": 8877,
    "currency": "SYP",
    "customer": "cum",
    "description": "My first Stripe payment",
    "status": "succeeded",
    "payment_method": "nihil",
    "receipt_email": "kirlin.brisa@yahoo.com"
}
```

You can also use dot notation to update nested values:

```php
$fixture = (new ExampleHttpFixture(['items.0.name' => 'John Doe']))->toJson();
```

This will produce a response where the first item's name is set to "John Doe".

---

## 5. Fixture options

The output of the fixture can be in  JSON, XML, array and a Laravel collection.

### XML

If you want to return XML instead of JSON, you can use the `toXML()` method and pass the root element name as a parameter.
For example:

```php
Http::fake([
    "https://www.example.com/get-user/harry" => Http::response(
    (new ExampleHttpFixture())->toXml('yourRootElement'), 
    200),
]);
```

This will return na XML response similar to:

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
