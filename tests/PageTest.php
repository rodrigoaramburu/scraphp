<?php

declare(strict_types=1);

use ScraPHP\HttpClient\Guzzle\GuzzleHttpClient;
use ScraPHP\HttpClient\HtmlElement;
use ScraPHP\Page;

beforeEach(fn () => $this->httpClient = new GuzzleHttpClient());

test('filter elements by tag name', function () {

    $page = new Page(
        url: 'http://localhost:8000/seletors.html',
        content: file_get_contents(__DIR__.'/fixtures/seletors.html'),
        statusCode: 200,
        headers: [],
        httpClient: $this->httpClient
    );

    $text = $page->filterCSS('h1')->text();

    expect($text)->toBe('Teste Seletores Titulo');
});

test('filter elements by class', function () {

    $page = new Page(
        url: 'http://localhost:8000/seletors.html',
        content: file_get_contents(__DIR__.'/fixtures/seletors.html'),
        statusCode: 200,
        headers: [],
        httpClient: $this->httpClient
    );

    $text = $page->filterCSS('.paragrafo')->text();

    expect($text)->toBe('Lorem ipsum dolor sit amet consectetur.');
});

test('get attribute from element', function () {
    $page = new Page(
        url: 'http://localhost:8000/seletors.html',
        content: file_get_contents(__DIR__.'/fixtures/seletors.html'),
        statusCode: 200,
        headers: [],
        httpClient: $this->httpClient
    );

    $attr = $page->filterCSS('a')->attr('href');

    expect($attr)->toBe('https://www.google.com');
});

test('iterate filtered elements', function () {
    $page = new Page(
        url: 'http://localhost:8000/seletors.html',
        content: file_get_contents(__DIR__.'/fixtures/seletors.html'),
        statusCode: 200,
        headers: [],
        httpClient: $this->httpClient
    );

    $result = $page->filterCSSEach('ul li', function (HtmlElement $element, int $i) {
        return $element->text().' - '.$i;
    });

    expect($result)->toBe(['Item 1 - 0', 'Item 2 - 1', 'Item 3 - 2']);
});

test('chain css filter ', function () {
    $page = new Page(
        url: 'http://localhost:8000/seletors.html',
        content: file_get_contents(__DIR__.'/fixtures/seletors.html'),
        statusCode: 200,
        headers: [],
        httpClient: $this->httpClient
    );

    $text = $page->filterCSS('ul')->filterCSS('li')->text();

    expect($text)->toBe('Item 1');
});

test('chain css filterEach ', function () {
    $page = new Page(
        url: 'http://localhost:8000/seletors.html',
        content: file_get_contents(__DIR__.'/fixtures/seletors.html'),
        statusCode: 200,
        headers: [],
        httpClient: $this->httpClient
    );

    $resutl = $page->filterCSS('ul')->filterCSSEach('li', function (HtmlElement $element, int $i) {
        return $element->text().' - '.$i;
    });

    expect($resutl)->toBe(['Item 1 - 0', 'Item 2 - 1', 'Item 3 - 2']);
});
