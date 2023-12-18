<?php

declare(strict_types=1);

use Monolog\Logger;
use ScraPHP\ScraPHP;
use ScraPHP\Writers\Writer;
use ScraPHP\Writers\JsonWriter;
use ScraPHP\HttpClient\HttpClient;
use ScraPHP\HttpClient\Guzzle\GuzzleHttpClient;
use ScraPHP\HttpClient\WebDriver\WebDriverHttpClient;

afterEach(function () {
    if (file_exists('out.json')) {
        unlink('out.json');
    }
});

test('create a scraphp instance with attributes', function () {

    $scraphp = ScraPHP::build()->create();

    expect($scraphp)->toBeInstanceOf(ScraPHP::class);
    expect($scraphp->httpClient())->toBeInstanceOf(GuzzleHttpClient::class);
    expect($scraphp->logger())->toBeInstanceOf(Logger::class);
    expect($scraphp->writer())->toBeInstanceOf(JsonWriter::class);
    expect($scraphp->retryCount())->toBe(3);
    expect($scraphp->retryTime())->toBe(30);

});

test('create a scraphp instance passing attributes', function () {

    $httpClient = Mockery::mock(HttpClient::class);
    $logger = Mockery::mock(Logger::class);
    $writer = Mockery::mock(Writer::class);

    $scraphp = ScraPHP::build()
        ->withHttpClient($httpClient)
        ->withLogger($logger)
        ->withWriter($writer)
        ->create();

    expect($scraphp)->toBeInstanceOf(ScraPHP::class);
    expect($scraphp->httpClient())->toBe($httpClient);
    expect($scraphp->logger())->toBe($logger);
    expect($scraphp->writer())->toBe($writer);
});

test('create a scraphp instance passing a filename for the logger', function () {

    $scraphp = ScraPHP::build()
        ->withLogger('test.log')
        ->create();

    $filename = $scraphp->logger()->getHandlers()[0]->getUrl();

    expect($filename)->toEndWith('test.log');

});

test('pass retryTime and retryCount', function () {
    $scraphp = ScraPHP::build()
        ->withRetryTime(15)
        ->withRetryCount(5)
        ->create();

    expect($scraphp->retryTime())->toBe(15);
    expect($scraphp->retryCount())->toBe(5);
});


test('create a scraphp instance with webdriver', function () {

    $scraphp = ScraPHP::build()
        ->withWebDriver('http://localhost:4444')
        ->create();

    expect($scraphp)->toBeInstanceOf(ScraPHP::class);
    expect($scraphp->httpClient())->toBeInstanceOf(WebDriverHttpClient::class);
});
