<?php

declare(strict_types=1);

use Monolog\Logger;
use ScraPHP\ScraPHP;
use ScraPHP\Writers\Writer;
use ScraPHP\Writers\CSVWriter;
use ScraPHP\Writers\JsonWriter;
use ScraPHP\HttpClient\HttpClient;
use ScraPHP\HttpClient\Guzzle\GuzzleHttpClient;
use ScraPHP\HttpClient\WebDriver\WebDriverHttpClient;
use ScraPHP\Writers\DatabaseWriter;

afterEach(function () {

    $files = ['out.json', 'file.json', 'file.cvs'];
    foreach ($files as $file) {
        if (file_exists($file)) {
            unlink($file);
        }
    }

});

test('create a scraphp instance with attributes', function () {

    $scraphp = ScraPHP::build()->create();

    expect($scraphp)->toBeInstanceOf(ScraPHP::class);
    expect($scraphp->httpClient())->toBeInstanceOf(GuzzleHttpClient::class);
    expect($scraphp->logger())->toBeInstanceOf(Logger::class);
    expect($scraphp->writer())->toBeInstanceOf(JsonWriter::class);

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

test('create a scraphp instance with webdriver', function () {

    $scraphp = ScraPHP::build()
        ->withWebDriver('http://localhost:4444')
        ->create();

    expect($scraphp)->toBeInstanceOf(ScraPHP::class);
    expect($scraphp->httpClient())->toBeInstanceOf(WebDriverHttpClient::class);
});

test('create a scraphp instance with jsonwriter', function () {

    $scraphp = ScraPHP::build()
        ->withJsonWriter('file.json')
        ->create();

    expect($scraphp)->toBeInstanceOf(ScraPHP::class);
    expect($scraphp->writer())->toBeInstanceOf(JsonWriter::class);
    expect($scraphp->writer())
        ->filename()->toBe('file.json');
});

test('create a scraphp instance with csvwriter', function () {

    $scraphp = ScraPHP::build()
        ->withCSVWriter('file.cvs', ['title', 'content'], ',')
        ->create();

    expect($scraphp)->toBeInstanceOf(ScraPHP::class);
    expect($scraphp->writer())->toBeInstanceOf(CSVWriter::class);
    expect($scraphp->writer())
        ->filename()->toBe('file.cvs')
        ->header()->toBe(['title', 'content'])
        ->separator()->toBe(',');
});

test('create a scraphp instance with databasewriter', function () {

    $con = Mockery::mock(\PDO::class);

    $scraphp = ScraPHP::build()
        ->withDatabaseWriter($con, 'posts')
        ->create();

    expect($scraphp)->toBeInstanceOf(ScraPHP::class);
    expect($scraphp->writer())->toBeInstanceOf(DatabaseWriter::class);
});
