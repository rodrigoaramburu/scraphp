<?php

declare(strict_types=1);

use ScraPHP\Page;
use ScraPHP\Exceptions\UrlNotFoundException;
use ScraPHP\Exceptions\AssetNotFoundException;
use ScraPHP\HttpClient\Guzzle\GuzzleHttpClient;

test('retrive a webpage and return an object page', function () {

    $guzzleClient = new GuzzleHttpClient();

    $page = $guzzleClient->get('http://localhost:8000/hello-world.php');

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

    $guzzleClient = new GuzzleHttpClient();

    $content = $guzzleClient->fetchAsset('http://localhost:8000/texto.txt');

    expect($content)->toBe('Hello World');
});

test('throw exception if asset not found', function () {
    $guzzleClient = new GuzzleHttpClient();

    $content = $guzzleClient->fetchAsset('http://localhost:8000/not-found.txt');
})->throws(AssetNotFoundException::class);


test('throw exception if url not found', function () {
    $guzzleClient = new GuzzleHttpClient();

    $guzzleClient->get('http://localhost:8000/not-found.txt');
})->throws(UrlNotFoundException::class);
