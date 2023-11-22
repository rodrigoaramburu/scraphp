<?php

declare(strict_types=1);

use ScraPHP\Page;
use Psr\Log\LoggerInterface;
use ScraPHP\Exceptions\HttpClientException;
use ScraPHP\Exceptions\UrlNotFoundException;
use ScraPHP\Exceptions\AssetNotFoundException;
use ScraPHP\HttpClient\Guzzle\GuzzleHttpClient;

beforeEach(function () {

    $this->logger = Mockery::mock(LoggerInterface::class);
    $this->guzzleClient = new GuzzleHttpClient($this->logger);

});

test('retrive a webpage and return an object page', function () {

    $this->logger->shouldReceive('info')->with('Accessing http://localhost:8000/hello-world.php');
    $this->logger->shouldReceive('info')->with('Status: 200 http://localhost:8000/hello-world.php');

    $page = $this->guzzleClient->get('http://localhost:8000/hello-world.php');

    expect($page)->toBeInstanceOf(Page::class);
    expect($page->statusCode())->toBe(200);
    expect($page->url())->toBe('http://localhost:8000/hello-world.php');
    expect($page->headers('my-header'))->toContain(['teste']);
    expect($page->header('my-header'))->toBe(['teste']);

    expect($page->content())->toBe(<<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <title>Página Teste</title>
</head>
<body>
    <h1>Hello World</h1>
</body>
</html>
HTML);

});

test('fetch an asset', function () {

    $this->logger->shouldReceive('info')->with('Fetching asset http://localhost:8000/texto.txt');
    $this->logger->shouldReceive('info')->with('Status: 200 http://localhost:8000/texto.txt');

    $content = $this->guzzleClient->fetchAsset('http://localhost:8000/texto.txt');

    expect($content)->toBe('Hello World');
});

test('throw exception if asset not found', function () {

    $this->logger->shouldReceive('info')->with('Fetching asset http://localhost:8000/not-found.txt');

    $this->logger->shouldReceive('error')->with('404 NOT FOUND http://localhost:8000/not-found.txt');

    $this->guzzleClient->fetchAsset('http://localhost:8000/not-found.txt');

})->throws(AssetNotFoundException::class);

test('throw exception if url not found', function () {

    $this->logger->shouldReceive('info')->with('Accessing http://localhost:8000/not-found.php');

    $this->logger->shouldReceive('error')->with('404 NOT FOUND http://localhost:8000/not-found.php');

    $this->guzzleClient->get('http://localhost:8000/not-found.php');

})->throws(UrlNotFoundException::class);


test('throw exception if http client error', function () {
    $this->logger->shouldReceive('info')->with('Accessing http://scraphp.com.br:8321/not-found.php');

    $this->guzzleClient->get('http://scraphp.com.br:8321/not-found.php');
})->throws(HttpClientException::class);


test('throw exception if http client error on fetchAsset', function () {
    $this->logger->shouldReceive('info')->with('Fetching asset http://scraphp.com.br:8321/not-found.jpg');

    $this->guzzleClient->fetchAsset('http://scraphp.com.br:8321/not-found.jpg');
})->throws(HttpClientException::class);
