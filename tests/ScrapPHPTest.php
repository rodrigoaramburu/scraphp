<?php

declare(strict_types=1);

use ScraPHP\Page;
use ScraPHP\ScraPHP;
use ScraPHP\ProcessPage;
use Psr\Log\LoggerInterface;
use ScraPHP\Writers\JsonWriter;
use Scraphp\HttpClient\HttpClient;
use ScraPHP\HttpClient\Guzzle\GuzzleHttpClient;

beforeEach(function () {
    $this->httpClient = Mockery::mock(HttpClient::class);
    $this->httpClient->shouldReceive('withLogger')->andReturn($this->httpClient);

    $this->scraphp = new ScraPHP();
    $this->scraphp->withHttpClient($this->httpClient);
});

afterEach(function () {

    $files = [
        __DIR__.'/assets/texto.txt',
        __DIR__.'/assets/my-filename.txt',
        __DIR__.'/assets/log.txt',
    ];
    foreach ($files as $file) {
        if (file_exists($file)) {
            unlink($file);
        }
    }
});

test('go to a page and return the body', function () {

    $this->httpClient->shouldReceive('get')
        ->once()
        ->with('https://localhost:8000/teste.html')
        ->andReturn(new Page(
            content: '<h1>Hello World</h1>',
            statusCode: 200,
            headers: [],
            url: 'https://localhost:8000/teste.html',
            httpClient: $this->httpClient
        ));

    $this->scraphp->go('https://localhost:8000/teste.html', function (Page $page) {
        expect($page)->toBeInstanceOf(Page::class);
        expect($page->content())->toBe('<h1>Hello World</h1>');
        expect($page->statusCode())->toBe(200);
        expect($page->headers())->toBe([]);
        expect($page->url())->toBe('https://localhost:8000/teste.html');

    });

});

test('bind the context if the callback is a closure', function () {

    $this->httpClient->shouldReceive('get')
        ->once()
        ->with('https://localhost:8000/teste.html')
        ->andReturn(new Page(
            content: '<h1>Hello World</h1>',
            statusCode: 200,
            headers: [],
            url: 'https://localhost:8000/teste.html',
            httpClient: $this->httpClient
        ));

    $this->scraphp->go('https://localhost:8000/teste.html', function (Page $page) {
        expect($this)->toBeInstanceOf(ScraPHP::class);
    });

});

test('default http client should be GuzzleHttpClient', function () {
    $scraphp = new ScraPHP();
    expect($scraphp->httpClient())->toBeInstanceOf(GuzzleHttpClient::class);
});

test('call fetch an asset from httpClient', function () {

    $this->httpClient->shouldReceive('fetchAsset')
        ->once()
        ->with('https://localhost:8000/texto.txt')
        ->andReturn('Hello World');

    $content = $this->scraphp->fetchAsset('https://localhost:8000/texto.txt');

    expect($content)->toBe('Hello World');

});

test('call save asset with default filename', function () {

    $this->httpClient->shouldReceive('fetchAsset')
        ->once()
        ->with('https://localhost:8000/texto.txt')
        ->andReturn('Hello World');

    $file = $this->scraphp->saveAsset('https://localhost:8000/texto.txt', __DIR__.'/assets/');

    expect($file)->toBeFile();
    expect(file_get_contents($file))->toBe('Hello World');
});

test('call save asset with custom filename', function () {

    $this->httpClient->shouldReceive('fetchAsset')
        ->once()
        ->with('https://localhost:8000/texto.txt')
        ->andReturn('Hello World');

    $file = $this->scraphp->saveAsset('https://localhost:8000/texto.txt', __DIR__.'/assets/', 'my-filename.txt');

    expect($file)->toBeFile();
    expect(file_get_contents($file))->toBe('Hello World');
});

test('log to a file', function () {
    $scraphp = new ScraPHP([
        'logger' => ['filename' => __DIR__.'/assets/log.txt'],
    ]);

    $scraphp->logger()->debug('Teste');

    expect(__DIR__.'/assets/log.txt')->toBeFile();
    expect(file_get_contents(__DIR__.'/assets/log.txt'))->toContain('Teste');

});

test('inject the logger into the writer', function () {

    $scraphp = new ScraPHP();

    $scraphp->withWriter(new JsonWriter(__DIR__.'/assets/log.txt'));

    expect($scraphp->writer()->logger())->toBeInstanceOf(LoggerInterface::class);
});


test('call class ProcessPage', function () {

    $httpClient = Mockery::mock(HttpClient::class);
    $httpClient->shouldReceive('get')->andReturn(new Page(
        content: '<h1>Hello World</h1>',
        statusCode: 200,
        headers: [],
        url: 'https://localhost:8000/teste.html',
        httpClient: $httpClient
    ));
    $httpClient->shouldReceive('withLogger')->once();
    $scraphp = new ScraPHP();
    $scraphp->withHttpClient($httpClient);


    $pp =  Mockery::mock(ProcessPage::class);
    $pp->shouldReceive('withScraPHP')->once()->with($scraphp);
    $pp->shouldReceive('process')->once();

    $scraphp->go('https://localhost:8000/teste.html', $pp);

});
