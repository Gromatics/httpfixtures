<?php

use Gromatics\Httpfixtures\Services\FileModificationService;
use Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

it('should copy ExampleHttpFixture and change the classname', function () {
    FileModificationService::copyExampleHttpFixture('PestTestClassNameFixture');

    $fixturePath = base_path('tests/Fixtures/PestTestClassNameFixture.php');
    expect(File::exists($fixturePath))->toBeTrue();

    /** @phpstan-ignore-next-line */
    $instance = new Tests\Fixtures\PestTestClassNameFixture;
    /** @phpstan-ignore-next-line */
    expect($instance)->toBeInstanceOf(Tests\Fixtures\PestTestClassNameFixture::class);

    $definition = $instance->definition();
    expect($definition)->toHaveKeys(['status', 'message', 'items']);

    File::delete($fixturePath);
    expect(File::exists($fixturePath))->toBeFalse();
});

it('should copy ExampleHttpFixture and set definitions according /Fixtures/stripe-result.json', function () {
    $json = file_get_contents(dirname(__FILE__).'/Fixtures/stripe-result.json');
    FileModificationService::copyExampleHttpFixture('PestTestFixture', $json, false);

    $fixturePath = base_path('tests/Fixtures/PestTestFixture.php');
    expect(File::exists($fixturePath))->toBeTrue();

    /** @phpstan-ignore-next-line */
    $instance = new Tests\Fixtures\PestTestFixture;
    /** @phpstan-ignore-next-line */
    expect($instance)->toBeInstanceOf(Tests\Fixtures\PestTestFixture::class);

    $definition = $instance->definition();
    expect($definition)->toHaveKeys(['object', 'amount', 'charges']);
    expect($definition['charges']['data'][0]['status'])->toBe('succeeded');

    File::delete($fixturePath);
    expect(File::exists($fixturePath))->toBeFalse();
});

it('should copy ExampleHttpFixture and set definitions according /Fixtures/stripe-result.json and replace with faker data', function () {
    $json = file_get_contents(dirname(__FILE__).'/Fixtures/stripe-result.json');
    FileModificationService::copyExampleHttpFixture('PestTestStripeFixture', $json, true);

    $fixturePath = base_path('tests/Fixtures/PestTestStripeFixture.php');
    expect(File::exists($fixturePath))->toBeTrue();

    /** @phpstan-ignore-next-line */
    $instance = new Tests\Fixtures\PestTestStripeFixture;
    /** @phpstan-ignore-next-line */
    expect($instance)->toBeInstanceOf(Tests\Fixtures\PestTestStripeFixture::class);

    $definition = $instance->definition();

    expect($definition)->toHaveKeys(['object', 'amount', 'charges']);

    expect($definition['charges']['data'][0]['status'])->toBe('succeeded');
    expect($definition['receipt_email'])->not()->toBe('jenny.rosen@example.com');
    expect(filter_var($definition['receipt_email'], FILTER_VALIDATE_EMAIL))->not()->toBeFalse();

    File::delete($fixturePath);
    expect(File::exists($fixturePath))->toBeFalse();
});

function getCharType($char) {
    if (preg_match('/[0-9]/', $char)) {
        return "number";
    } elseif (preg_match('/[a-zA-Z]/', $char)) {
        return "letter";
    } elseif (preg_match('/\s/', $char)) {
        return "whitespace";
    } else {
        return "symbol";
    }
}

it('should generate faker identifiers in the same format as the original', function () {
    $arr = [
        'identifier1' => '383fe3fb-6c04-4b77-8bde-c7feab01e662', //uui
        'identifier2' => 'A5C0Z8AK0E',
        'identifier3' => 'Addd234S##$cdfdd',
        'identifier4' => 'pi_3NLxg2L1ZLzhUeQv0EXAMPLE',
        'identifier5' => '17969_6414291_03098ab8-faf5-4e92-ae5c-87f3e92f1628',

    ];
    FileModificationService::copyExampleHttpFixture('PestTestIdentifiersFixture', json_encode($arr), true);

    $fixturePath = base_path('tests/Fixtures/PestTestIdentifiersFixture.php');
    expect(File::exists($fixturePath))->toBeTrue();

    /** @phpstan-ignore-next-line */
    $instance = new Tests\Fixtures\PestTestIdentifiersFixture;
    /** @phpstan-ignore-next-line */
    expect($instance)->toBeInstanceOf(Tests\Fixtures\PestTestIdentifiersFixture::class);

    $definition = $instance->definition();
    expect(strlen($definition['identifier1']))->toBe(36);
    expect(strlen($definition['identifier2']))->toBe(10);
    expect(strlen($definition['identifier3']))->toBe(16);
    expect(strlen($definition['identifier4']))->toBe(27);
    expect(strlen($definition['identifier5']))->toBe(50);

    expect(getCharType($definition['identifier2'][1]))->toBe('number');
    expect(getCharType($definition['identifier4'][2]))->toBe('symbol');

    $expl = explode('-', str_replace('_', '-', $definition['identifier5']));
    expect(count($expl))->toBe(7);

    File::delete($fixturePath);
    expect(File::exists($fixturePath))->toBeFalse();
});
