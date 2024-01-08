<?php


declare(strict_types=1);

use ScraPHP\ScraPHP;
use ScraPHP\Writers\Writer;
use Psr\Log\LoggerInterface;
use ScraPHP\HttpClient\Page;
use ScraPHP\HttpClient\HttpClient;
use ScraPHP\Midleware\LogMiddleware;

beforeEach(function () {
    $this->logMiddleware = new LogMiddleware();

    $this->logger = Mockery::mock(LoggerInterface::class);
    $httpClient = Mockery::mock(HttpClient::class);
    $scraphp = new ScraPHP(
        logger: $this->logger,
        httpClient: $httpClient,
        writer: Mockery::mock(Writer::class)
    );

    $this->logMiddleware->withScraPHP($scraphp);
});


test('log go action', function () {

    $this->logger->shouldReceive('info')->with('Accessing: url');
    $this->logger->shouldReceive('info')->with('Accessed: 200 url');

    $page = $this->logMiddleware->processGo('url', function (string $url) {
        $page = Mockery::mock(Page::class);
        $page->shouldReceive('statusCode')->andReturn(200);
        return $page;
    });

    expect($page)->toBeInstanceOf(Page::class);

});

test('log fetch asset', function () {

    $this->logger->shouldReceive('info')->with('Fetching: url');
    $this->logger->shouldReceive('info')->with('Fetched: url');

    $result = $this->logMiddleware->processAssetFetch('url', function (string $url) {
        return 'ASDF';
    });

    expect($result)->toBe('ASDF');

});


test('log save asset fetch asset', function () {

    $this->logger->shouldReceive('info')->with('Saving asset: url');
    $this->logger->shouldReceive('info')->with('Asset Saved: filename.txt');

    $result = $this->logMiddleware->processSaveAsset('url', 'path', 'filename.txt', function (string $url, $path, $filename) {
        return 'filename.txt';
    });

    expect($result)->toBe('filename.txt');

});
