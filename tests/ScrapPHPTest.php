<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;
use ScraPHP\Exceptions\HttpClientException;
use ScraPHP\HttpClient\Guzzle\GuzzlePage;
use Scraphp\HttpClient\HttpClient;
use ScraPHP\HttpClient\Page;
use ScraPHP\ProcessPage;
use ScraPHP\ScraPHP;
use ScraPHP\Writers\Writer;

beforeEach(function () {
    $this->httpClient = Mockery::mock(HttpClient::class);
    $this->logger = Mockery::mock(LoggerInterface::class);
    $this->writer = Mockery::mock(Writer::class);

    $this->scraphp = new ScraPHP(
        httpClient: $this->httpClient,
        logger: $this->logger,
        writer: $this->writer,
    );
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
        ->andReturn(new GuzzlePage(
            content: '<h1>Hello World</h1>',
            statusCode: 200,
            headers: [],
            url: 'https://localhost:8000/teste.html'
        ));

    $this->logger
        ->shouldReceive('info')
        ->with('Accessing https://localhost:8000/teste.html');

    $this->logger
        ->shouldReceive('info')
        ->with('Status: 200 https://localhost:8000/teste.html');

    $this->scraphp->go('https://localhost:8000/teste.html', function (Page $page) {
        expect($page)->toBeInstanceOf(Page::class)
            ->htmlBody()->toBe('<h1>Hello World</h1>')
            ->statusCode()->toBe(200)
            ->headers()->toBe([])
            ->url()->toBe('https://localhost:8000/teste.html');

    });

});

test('bind the context if the callback is a closure', function () {

    $this->httpClient->shouldReceive('get')
        ->once()
        ->with('https://localhost:8000/teste.html')
        ->andReturn(new GuzzlePage(
            content: '<h1>Hello World</h1>',
            statusCode: 200,
            headers: [],
            url: 'https://localhost:8000/teste.html'
        ));

    $this->logger
        ->shouldReceive('info')
        ->with('Accessing https://localhost:8000/teste.html');

    $this->logger
        ->shouldReceive('info')
        ->with('Status: 200 https://localhost:8000/teste.html');

    $this->scraphp->go('https://localhost:8000/teste.html', function (Page $page) {
        expect($this)->toBeInstanceOf(ScraPHP::class);
    });

});

test('call fetch an asset from httpClient', function () {

    $this->httpClient
        ->shouldReceive('fetchAsset')
        ->once()
        ->with('https://localhost:8000/texto.txt')
        ->andReturn('Hello World');

    $this->logger
        ->shouldReceive('info')
        ->once()
        ->with('Fetching asset: https://localhost:8000/texto.txt');

    $this->logger
        ->shouldReceive('info')
        ->once()
        ->with('Fetched: https://localhost:8000/texto.txt');

    $content = $this->scraphp->fetchAsset('https://localhost:8000/texto.txt');

    expect($content)->toBe('Hello World');

});

test('call save asset with default filename', function () {

    $this->httpClient
        ->shouldReceive('fetchAsset')
        ->once()
        ->with('https://localhost:8000/texto.txt')
        ->andReturn('Hello World');

    $this->logger
        ->shouldReceive('info')
        ->once()
        ->with('Fetching asset: https://localhost:8000/texto.txt');

    $this->logger
        ->shouldReceive('info')
        ->once()
        ->with('Fetched: https://localhost:8000/texto.txt');

    $file = $this->scraphp->saveAsset('https://localhost:8000/texto.txt', __DIR__.'/assets/');

    expect($file)->toBeFile();
    expect(file_get_contents($file))->toBe('Hello World');
});

test('call save asset with custom filename', function () {

    $this->httpClient
        ->shouldReceive('fetchAsset')
        ->once()
        ->with('https://localhost:8000/texto.txt')
        ->andReturn('Hello World');

    $this->logger
        ->shouldReceive('info')
        ->once()
        ->with('Fetching asset: https://localhost:8000/texto.txt');

    $this->logger
        ->shouldReceive('info')
        ->once()
        ->with('Fetched: https://localhost:8000/texto.txt');

    $file = $this->scraphp->saveAsset('https://localhost:8000/texto.txt', __DIR__.'/assets/', 'my-filename.txt');

    expect($file)->toBeFile();
    expect(file_get_contents($file))->toBe('Hello World');
});

test('call class ProcessPage', function () {

    $this->logger
        ->shouldReceive('info')
        ->with('Accessing https://localhost:8000/teste.html');

    $this->logger
        ->shouldReceive('info')
        ->with('Status: 200 https://localhost:8000/teste.html');

    $this->httpClient->shouldReceive('get')
        ->andReturn(new GuzzlePage(
            content: '<h1>Hello World</h1>',
            statusCode: 200,
            headers: [],
            url: 'https://localhost:8000/teste.html'
        ));

    $pp = Mockery::mock(ProcessPage::class);
    $pp->shouldReceive('withScraPHP')->once()->with($this->scraphp);
    $pp->shouldReceive('process')->once();

    $this->scraphp->go('https://localhost:8000/teste.html', $pp);

});

test('retry get a url after a failed', function () {

    $this->httpClient
        ->shouldReceive('get')
        ->times(3)
        ->andReturnUsing(function () {
            static $counter = 0;
            if ($counter < 2) {
                $counter++;
                throw new HttpClientException('test');
            }

            return new GuzzlePage(
                content: '<h1>Hello World</h1>',
                statusCode: 200,
                headers: [],
                url: 'https://localhost:8000/teste.html'
            );
        });

    $scraphp = new ScraPHP(
        httpClient: $this->httpClient,
        logger: $this->logger,
        writer: $this->writer,
        retryCount: 3,
        retryTime: 1
    );

    $this->logger->shouldReceive('error');
    $this->logger->shouldReceive('info');

    $scraphp->go('http://localhost:8000/teste.html', function (Page $page) {

    });

    expect($scraphp->urlErrors())->toHaveCount(0);
});

test('save a failed url and its processor after tried 3 times', function () {

    $this->httpClient
        ->shouldReceive('get')
        ->times(3)
        ->andThrows(new HttpClientException('test'));

    $scraphp = new ScraPHP(
        httpClient: $this->httpClient,
        logger: $this->logger,
        writer: $this->writer,
        retryCount: 3,
        retryTime: 1
    );

    $this->logger->shouldReceive('error');
    $this->logger->shouldReceive('info');

    $scraphp->go('http://localhost:8000/teste.html', function (Page $page) {

    });

    expect($scraphp->urlErrors()[0]['url'])->toContain('http://localhost:8000/teste.html');
    expect($scraphp->urlErrors()[0]['pageProcessor'])->toBeInstanceOf(Closure::class);
});

test('retry get an asset if its fail', function () {

    $this->httpClient
        ->shouldReceive('fetchAsset')
        ->times(3)
        ->andReturnUsing(function () {
            static $counter = 0;
            if ($counter < 2) {
                $counter++;
                throw new HttpClientException('test');
            }

            return 'ABC';
        });

    $scraphp = new ScraPHP(
        httpClient: $this->httpClient,
        logger: $this->logger,
        writer: $this->writer,
        retryCount: 3,
        retryTime: 1
    );

    $this->logger->shouldReceive('error');
    $this->logger->shouldReceive('info');

    $scraphp->fetchAsset('https://localhost:8000/teste.jpg');

    expect($scraphp->assetErrors())->toHaveCount(0);

});

test('save a failed url asset tried 3 times', function () {

    $this->httpClient
        ->shouldReceive('fetchAsset')
        ->times(3)
        ->andThrows(new HttpClientException('test'));

    $scraphp = new ScraPHP(
        httpClient: $this->httpClient,
        logger: $this->logger,
        writer: $this->writer,
        retryCount: 3,
        retryTime: 1
    );

    $this->logger->shouldReceive('error');
    $this->logger->shouldReceive('info');

    $scraphp->fetchAsset('http://localhost:8000/teste.jpg');

    expect($scraphp->assetErrors()[0]['url'])->toContain('http://localhost:8000/teste.jpg');

});

test('save a failed url asset tried 3 times on saveAsset', function () {

    $this->httpClient
        ->shouldReceive('fetchAsset')
        ->times(3)
        ->andThrows(new HttpClientException('test'));

    $scraphp = new ScraPHP(
        httpClient: $this->httpClient,
        logger: $this->logger,
        writer: $this->writer,
        retryCount: 3,
        retryTime: 1
    );

    $this->logger->shouldReceive('error');
    $this->logger->shouldReceive('info');

    $scraphp->saveAsset('http://localhost:8000/teste.jpg', 'teste.jpg');

    expect($scraphp->assetErrors()[0]['url'])->toContain('http://localhost:8000/teste.jpg');

});
