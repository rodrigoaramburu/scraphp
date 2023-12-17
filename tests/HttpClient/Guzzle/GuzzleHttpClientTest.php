<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;
use ScraPHP\Exceptions\HttpClientException;
use ScraPHP\Exceptions\UrlNotFoundException;
use ScraPHP\HttpClient\Guzzle\GuzzleHttpClient;
use ScraPHP\HttpClient\Page;

beforeEach(function () {

    $this->logger = Mockery::mock(LoggerInterface::class);
    $this->guzzleClient = new GuzzleHttpClient($this->logger);

});

test('retrive a webpage and return an object page', function () {

    //$this->logger->shouldReceive('info')->with('Accessing http://localhost:8000/hello-world.php');
    //$this->logger->shouldReceive('info')->with('Status: 200 http://localhost:8000/hello-world.php');

    $page = $this->guzzleClient->get('http://localhost:8000/hello-world.php');

    expect($page)->toBeInstanceOf(Page::class)
        ->statusCode()->toBe(200)
        ->url()->toBe('http://localhost:8000/hello-world.php')
        ->headers('my-header')->toContain(['teste'])
        ->header('my-header')->toBe(['teste'])
        ->htmlBody()->toContain('<title>Página Teste</title>', '<h1>Hello World</h1>');

});

test('fetch an asset', function () {

    $content = $this->guzzleClient->fetchAsset('http://localhost:8000/asset-test.txt');

    expect($content)->toBe('Asset Test');
});

test('throw exception if url not found', function () {

    //$this->logger->shouldReceive('info')->with('Accessing http://localhost:8000/not-found.php');
    //$this->logger->shouldReceive('error')->with('404 NOT FOUND http://localhost:8000/not-found.php');

    $this->guzzleClient->get('http://localhost:8000/not-found.php');

})->throws(UrlNotFoundException::class);

test('throw exception if http client error', function () {
    $this->logger->shouldReceive('info')->with('Accessing http://scraphp.com.br:8321/not-found.php');

    $this->guzzleClient->get('http://scraphp.com.br:8321/not-found.php');
})->throws(HttpClientException::class);
