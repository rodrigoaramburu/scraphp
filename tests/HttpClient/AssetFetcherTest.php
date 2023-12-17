<?php

declare(strict_types=1);

use ScraPHP\Exceptions\AssetNotFoundException;
use ScraPHP\Exceptions\HttpClientException;
use ScraPHP\HttpClient\AssetFetcher;

beforeEach(function () {
    $this->assetFetcher = new AssetFetcher();
});

test('fetch an asset', function () {
    $content = $this->assetFetcher->fetchAsset('http://localhost:8000/asset-test.txt');
    expect($content)->toBe('Asset Test');
});

test('throw exception if asset not found', function () {
    $this->assetFetcher->fetchAsset('http://localhost:8000/not-found.txt');
})->throws(AssetNotFoundException::class);

test('throw exception if http client error on fetchAsset', function () {
    $this->assetFetcher->fetchAsset('http://scraphp.com.br:8321/not-found.jpg');
})->throws(HttpClientException::class);
