<?php

namespace Gromatics\tests;

use Gromatics\Httpfixtures\Services\HttpResponseRecorder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

it('creates a HTTP Fixture from a real JSON request', function () {

    Http::record();
    Http::get('https://api.stackexchange.com/2.2/search?order=desc&sort=activity&intitle=perl&site=stackoverflow&limit=1');
    HttpResponseRecorder::recordedToHttpFixture();

    $fixturePath = base_path('tests/Fixtures/StackexchangeSearchFixture.php');
    expect(File::exists($fixturePath))->toBeTrue();
    File::delete($fixturePath);

});
