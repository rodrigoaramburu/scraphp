<?php 

declare(strict_types=1);

use Psr\Log\LoggerInterface;
use ScraPHP\HttpClient\AssetFetcher;
use ScraPHP\Exceptions\HttpClientException;
use ScraPHP\Exceptions\AssetNotFoundException;


beforeEach(function(){
    $this->logger = Mockery::mock(LoggerInterface::class);
    $this->assetFetcher = new AssetFetcher($this->logger);

});

test('fetch an asset', function () {

    $this->logger->shouldReceive('info')->with('Fetching asset http://localhost:8000/asset-test.txt');
    $this->logger->shouldReceive('info')->with('Status: 200 http://localhost:8000/asset-test.txt');

    $content = $this->assetFetcher->fetchAsset('http://localhost:8000/asset-test.txt');

    expect($content)->toBe('Asset Test');
});


test('throw exception if asset not found', function () {

    $this->logger->shouldReceive('info')->with('Fetching asset http://localhost:8000/not-found.txt');

    $this->logger->shouldReceive('error')->with('404 NOT FOUND http://localhost:8000/not-found.txt');

    $this->assetFetcher->fetchAsset('http://localhost:8000/not-found.txt');

})->throws(AssetNotFoundException::class);


test('throw exception if http client error on fetchAsset', function () {
    $this->logger->shouldReceive('info')->with('Fetching asset http://scraphp.com.br:8321/not-found.jpg');

    $this->assetFetcher->fetchAsset('http://scraphp.com.br:8321/not-found.jpg');
})->throws(HttpClientException::class);