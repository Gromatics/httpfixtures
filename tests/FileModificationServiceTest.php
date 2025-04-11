<?php


use Tests\TestCase;

use Gromatics\HttpFixtures\Services\FileModificationService;
uses(TestCase::class)->in(__DIR__);


it('should copy ExampleHttpFixture and change the classname', function() {
    FileModificationService::copyExampleHttpFixture("PestTestClassNameFixture");

    $fixturePath = base_path('tests/Fixtures/PestTestClassNameFixture.php');
    expect(File::exists($fixturePath))->toBeTrue();

    /** @phpstan-ignore-next-line */
    $instance = new Tests\Fixtures\PestTestClassNameFixture();
    /** @phpstan-ignore-next-line */
    expect($instance)->toBeInstanceOf(Tests\Fixtures\PestTestClassNameFixture::class);

    $definition = $instance->definition();
    expect($definition)->toHaveKeys(['status', 'message', 'items']);

    File::delete($fixturePath);
    expect(File::exists($fixturePath))->toBeFalse();
});


it('should copy ExampleHttpFixture and set definitions according /Fixtures/stripe-result.json', function() {
    $json = file_get_contents(dirname(__FILE__) . '/Fixtures/stripe-result.json');
    FileModificationService::copyExampleHttpFixture("PestTestFixture", $json, false);

    $fixturePath = base_path('tests/Fixtures/PestTestFixture.php');
    expect(File::exists($fixturePath))->toBeTrue();

    /** @phpstan-ignore-next-line */
    $instance = new Tests\Fixtures\PestTestFixture();
    /** @phpstan-ignore-next-line */
    expect($instance)->toBeInstanceOf(Tests\Fixtures\PestTestFixture::class);

    $definition = $instance->definition();
    expect($definition)->toHaveKeys(['object', 'amount', 'charges']);
    expect($definition['charges']['data'][0]['status'])->toBe("succeeded");

    File::delete($fixturePath);
    expect(File::exists($fixturePath))->toBeFalse();
});


it('should copy ExampleHttpFixture and set definitions according /Fixtures/stripe-result.json and replace with faker data', function() {
    $json = file_get_contents(dirname(__FILE__) . '/Fixtures/stripe-result.json');
    FileModificationService::copyExampleHttpFixture("PestTestGoogleResultFixture", $json, true);

    $fixturePath = base_path('tests/Fixtures/PestTestGoogleResultFixture.php');
    expect(File::exists($fixturePath))->toBeTrue();

    /** @phpstan-ignore-next-line */
    $instance = new Tests\Fixtures\PestTestGoogleResultFixture();
    /** @phpstan-ignore-next-line */
    expect($instance)->toBeInstanceOf(Tests\Fixtures\PestTestGoogleResultFixture::class);

    $definition = $instance->definition();
    expect($definition)->toHaveKeys(['object', 'amount', 'charges']);

    expect($definition['charges']['data'][0]['status'])->toBe("succeeded");
    expect($definition['receipt_email'])->not()->toBe('jenny.rosen@example.com');
    expect(filter_var($definition['receipt_email'], FILTER_VALIDATE_EMAIL))->not()->toBeFalse();

    File::delete($fixturePath);
    expect(File::exists($fixturePath))->toBeFalse();
});
