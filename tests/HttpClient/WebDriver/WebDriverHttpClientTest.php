<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;
use ScraPHP\Exceptions\HttpClientException;
use ScraPHP\Exceptions\UrlNotFoundException;
use ScraPHP\HttpClient\WebDriver\WebDriverHttpClient;
use ScraPHP\HttpClient\WebDriver\WebDriverPage;

beforeEach(function () {
    $this->logger = Mockery::mock(LoggerInterface::class);
    $this->webDriverClient = new WebDriverHttpClient();
});

afterEach(function () {
    $this->webDriverClient->__destruct();
});

test('retrive a webpage and return an object page', function () {

    $page = $this->webDriverClient->get('http://localhost:8000/hello-world.php');

    expect($page)
        ->toBeInstanceOf(WebDriverPage::class)
        ->htmlBody()
        ->toContain('<title>Página Teste</title>', '<h1>Hello World</h1>')
        ->statusCode()
        ->toBe(200)
        ->url()
        ->toBe('http://localhost:8000/hello-world.php');

});

test('retrive a webpage and return an object page without h1', function () {

    $page = $this->webDriverClient->get('http://localhost:8000/paragraph.html');

    expect($page)
        ->toBeInstanceOf(WebDriverPage::class)
        ->htmlBody()
        ->toContain('<p>Lorem ipsum dolor sit amet consectetur.</p>')
        ->statusCode()
        ->toBe(200)
        ->url()
        ->toBe('http://localhost:8000/paragraph.html');

});

test('fetch an asset', function () {

    $this->webDriverClient = new WebDriverHttpClient();

    $content = $this->webDriverClient->fetchAsset('http://localhost:8000/asset-test.txt');

    expect($content)->toBe('Asset Test');
});

test('throw exception if url not found', function () {
    $this->webDriverClient->get('http://localhost:8000/not-found.php');

})->throws(UrlNotFoundException::class);

test('throw exception if http client error', function () {

    $this->webDriverClient->get('asdf');
})
->throws(HttpClientException::class);
