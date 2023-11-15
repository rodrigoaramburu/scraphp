<?php

declare(strict_types=1);

use ScraPHP\HttpClient\Guzzle\GuzzleHttpClient;
use Scraphp\HttpClient\HttpClient;
use ScraPHP\Page;
use ScraPHP\ScraPHP;

afterEach(function () {
    if(file_exists(__DIR__.'/assets/texto.txt')) {
        unlink(__DIR__.'/assets/texto-saved.txt');
    }
    if(file_exists(__DIR__.'/assets/my-filename.txt')) {
        unlink(__DIR__.'/assets/my-filename.txt');
    }
});

test('go to a page and return the body', function () {

    $httpClient = Mockery::mock(HttpClient::class);

    $httpClient->shouldReceive('get')
        ->once()
        ->with('https://localhost:8000/teste.html')
        ->andReturn(new Page(
            content: '<h1>Hello World</h1>',
            statusCode: 200,
            headers: [],
            url: 'https://localhost:8000/teste.html',
            httpClient: $httpClient
        ));

    $scraphp = new ScraPHP();
    $scraphp->withHttpClient($httpClient);

    $scraphp->go('https://localhost:8000/teste.html', function (Page $page) {
        expect($page)->toBeInstanceOf(Page::class);
        expect($page->content())->toBe('<h1>Hello World</h1>');
        expect($page->statusCode())->toBe(200);
        expect($page->headers())->toBe([]);
        expect($page->url())->toBe('https://localhost:8000/teste.html');

    });

});

test('bind the context if the callback is a closure', function () {

    $httpClient = Mockery::mock(HttpClient::class);

    $httpClient->shouldReceive('get')
        ->once()
        ->with('https://localhost:8000/teste.html')
        ->andReturn(new Page(
            content: '<h1>Hello World</h1>',
            statusCode: 200,
            headers: [],
            url: 'https://localhost:8000/teste.html',
            httpClient: $httpClient
        ));

    $scraphp = new ScraPHP();
    $scraphp->withHttpClient($httpClient);

    $scraphp->go('https://localhost:8000/teste.html', function (Page $page) {
        expect($this)->toBeInstanceOf(ScraPHP::class);
    });

});

test('default http client should be GuzzleHttpClient', function () {
    $scraphp = new ScraPHP();

    expect($scraphp->httpClient())->toBeInstanceOf(GuzzleHttpClient::class);
});


test('call featch an asset from httpClient', function () {

    $httpClient = Mockery::mock(HttpClient::class);

    $httpClient->shouldReceive('fetchAsset')
        ->once()
        ->with('https://localhost:8000/texto.txt')
        ->andReturn('Hello World');

    $scraphp = new ScraPHP();
    $scraphp->withHttpClient($httpClient);

    $content = $scraphp->fetchAsset('https://localhost:8000/texto.txt');

    expect($content)->toBe('Hello World');

});


test('call save asset with default filename', function () {

    $httpClient = Mockery::mock(HttpClient::class);

    $httpClient->shouldReceive('fetchAsset')
        ->once()
        ->with('https://localhost:8000/texto.txt')
        ->andReturn('Hello World');

    $scraphp = new ScraPHP();
    $scraphp->withHttpClient($httpClient);

    $file = $scraphp->saveAsset('https://localhost:8000/texto.txt', __DIR__ . '/assets');

    expect($file)->toBeFile();
    expect(file_get_contents($file))->toBe('Hello World');
});

test('call save asset with custom filename', function () {

    $httpClient = Mockery::mock(HttpClient::class);

    $httpClient->shouldReceive('fetchAsset')
        ->once()
        ->with('https://localhost:8000/texto.txt')
        ->andReturn('Hello World');

    $scraphp = new ScraPHP();
    $scraphp->withHttpClient($httpClient);

    $file = $scraphp->saveAsset('https://localhost:8000/texto.txt', __DIR__ . '/assets', 'my-filename.txt');

    expect($file)->toBeFile();
    expect(file_get_contents($file))->toBe('Hello World');
});
