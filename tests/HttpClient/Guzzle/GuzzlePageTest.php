<?php

declare(strict_types=1);

use ScraPHP\HttpClient\FilteredElement;
use ScraPHP\HttpClient\Guzzle\GuzzlePage;
use ScraPHP\Exceptions\InvalidLinkException;
use ScraPHP\Exceptions\InvalidImageException;

test('have attributes', function () {
    $page = new GuzzlePage(
        url: 'http://localhost:8000/hello-world.php',
        content: file_get_contents(__DIR__.'/../../test-pages/hello-world.php'),
        statusCode: 200,
        headers: [],
    );

    expect($page)->toBeInstanceOf(GuzzlePage::class)
        ->url()->toBe('http://localhost:8000/hello-world.php')
        ->statusCode()->toBe(200)
        ->htmlBody()->toContain('<title>Página Teste</title>', '<h1>Hello World</h1>')
        ->headers()->toBeArray()
        ->headers()->toBe([]);

});

test('filter elements by tag name', function () {

    $page = new GuzzlePage(
        url: 'http://localhost:8000/seletors.html',
        content: file_get_contents(__DIR__.'/../../test-pages/seletors.html'),
        statusCode: 200,
        headers: [],
    );

    $text = $page->filterCSS('h1')->text();

    expect($text)->toBe('Teste Seletores Titulo');
});

test('filter elements by class', function () {

    $page = new GuzzlePage(
        url: 'http://localhost:8000/seletors.html',
        content: file_get_contents(__DIR__.'/../../test-pages/seletors.html'),
        statusCode: 200,
        headers: [],
    );

    $text = $page->filterCSS('.paragrafo')->text();

    expect($text)->toBe('Lorem ipsum dolor sit amet consectetur.');
});

test('get attribute from element', function () {
    $page = new GuzzlePage(
        url: 'http://localhost:8000/seletors.html',
        content: file_get_contents(__DIR__.'/../../test-pages/seletors.html'),
        statusCode: 200,
        headers: [],
    );

    $attr = $page->filterCSS('a')->attr('href');

    expect($attr)->toBe('https://www.google.com');
});

test('iterate filtered elements', function () {
    $page = new GuzzlePage(
        url: 'http://localhost:8000/seletors.html',
        content: file_get_contents(__DIR__.'/../../test-pages/seletors.html'),
        statusCode: 200,
        headers: [],
    );

    $result = $page->filterCSSEach('ul li', function (FilteredElement $element, int $i) {
        return $element->text().' - '.$i;
    });

    expect($result)->toBe(['Item 1 - 0', 'Item 2 - 1', 'Item 3 - 2']);
});

test('chain filterCSS', function () {
    $page = new GuzzlePage(
        url: 'http://localhost:8000/seletors.html',
        content: file_get_contents(__DIR__.'/../../test-pages/seletors.html'),
        statusCode: 200,
        headers: [],
    );

    $text = $page->filterCSS('ul')->filterCSS('li')->text();

    expect($text)->toBe('Item 1');
});

test('chain css filterCSS with filterCSSEach ', function () {
    $page = new GuzzlePage(
        url: 'http://localhost:8000/seletors.html',
        content: file_get_contents(__DIR__.'/../../test-pages/seletors.html'),
        statusCode: 200,
        headers: [],
    );

    $result = $page->filterCSS('ul')->filterCSSEach('li', function (FilteredElement $element, int $i) {
        return $element->text().' - '.$i;
    });

    expect($result)->toBe(['Item 1 - 0', 'Item 2 - 1', 'Item 3 - 2']);
});

test('chain css filterEach with filterCSS', function () {
    $page = new GuzzlePage(
        url: 'http://localhost:8000/seletors.html',
        content: file_get_contents(__DIR__.'/../../test-pages/seletors.html'),
        statusCode: 200,
        headers: [],
    );

    $result = $page->filterCSSEach('.data', function (FilteredElement $element, int $i) {
        return $element->filterCSS('.nome')->text();
    });

    expect($result)->toBe(['Anderson', 'Carlos', 'Rafael']);
});

test('return null when filter element not found', function () {
    $page = new GuzzlePage(
        url: 'http://localhost:8000/seletors.html',
        content: file_get_contents(__DIR__.'/../../test-pages/seletors.html'),
        statusCode: 200,
        headers: [],
    );

    $result = $page->filterCSS('.not-found');

    expect($result)->toBeNull();
});

test('return null when filter element not found in chain', function () {
    $page = new GuzzlePage(
        url: 'http://localhost:8000/seletors.html',
        content: file_get_contents(__DIR__.'/../../test-pages/seletors.html'),
        statusCode: 200,
        headers: [],
    );

    $result = $page->filterCSS('.ul .not-found');

    expect($result)->toBeNull();
});


test('have title', function () {
    $page = new GuzzlePage(
        url: 'http://localhost:8000/hello-world.php',
        content: file_get_contents(__DIR__.'/../../test-pages/hello-world.php'),
        statusCode: 200,
        headers: [],
    );

    expect($page)->toBeInstanceOf(GuzzlePage::class)
        ->title()->toBe('Página Teste');

});

test('get a link data', function () {
    $page = new GuzzlePage(
        url: 'http://localhost:8000/folder1/folder2/link.html',
        content: file_get_contents(__DIR__.'/../../test-pages/folder1/folder2/link.html'),
        statusCode: 200,
        headers: [],
    );

    $link = $page->filterCSS('#link1')->link();

    expect($link)
        ->text()->toBe('Link 1')
        ->uri()->toBe('http://localhost:8000/folder1/folder2/page1.html?param1=value1&param2=value2')
        ->rawUri()->toBe('http://localhost:8000/folder1/folder2/page1.html?param1=value1&param2=value2')
        ->query()->toBe(['param1' => 'value1', 'param2' => 'value2']);
});

test('get a link uri without domain', function () {
    $page = new GuzzlePage(
        url: 'http://localhost:8000/folder1/folder2/link.html',
        content: file_get_contents(__DIR__.'/../../test-pages/folder1/folder2/link.html'),
        statusCode: 200,
        headers: [],
    );

    $link = $page->filterCSS('#link2')->link();

    expect($link)
        ->text()->toBe('Link 2')
        ->uri()->toBe('http://localhost:8000/folder1/folder2/page2.html');
});

test('get a link uri relative', function () {
    $page = new GuzzlePage(
        url: 'http://localhost:8000/folder1/folder2/link.html',
        content: file_get_contents(__DIR__.'/../../test-pages/folder1/folder2/link.html'),
        statusCode: 200,
        headers: [],
    );

    $link = $page->filterCSS('#link3')->link();

    expect($link)
        ->text()->toBe('Link 3')
        ->uri()->toBe('http://localhost:8000/folder1/folder2/page3.html');
});


test('throw exception if link is invalid', function () {

    $page = new GuzzlePage(
        url: 'http://localhost:8000/folder1/folder2/link.html',
        content: file_get_contents(__DIR__.'/../../test-pages/folder1/folder2/link.html'),
        statusCode: 200,
        headers: [],
    );

    $link = $page->filterCSS('#not-link')->link();

})->throws(InvalidLinkException::class);


test('get a image data', function () {
    $page = new GuzzlePage(
        url: 'http://localhost:8000/folder1/folder2/image.html',
        content: file_get_contents(__DIR__.'/../../test-pages/folder1/folder2/image.html'),
        statusCode: 200,
        headers: [],
    );

    $link = $page->filterCSS('#img1')->image();

    expect($link)
        ->source()->toBe('http://localhost:8000/folder1/folder2/image.png')
        ->rawUri()->toBe('http://localhost:8000/folder1/folder2/image.png')
        ->alt()->toBe('Test Image')
        ->width()->toBe(300)
        ->height()->toBe(200);
});

test('throw exception if image is invalid', function () {

    $page = new GuzzlePage(
        url: 'http://localhost:8000/folder1/folder2/image.html',
        content: file_get_contents(__DIR__.'/../../test-pages/folder1/folder2/image.html'),
        statusCode: 200,
        headers: [],
    );

    $link = $page->filterCSS('#not-image')->image();

})->throws(InvalidImageException::class);

test('get a text by regex', function () {

    $page = new GuzzlePage(
        url: 'http://localhost:8000/regex-test.html',
        content: file_get_contents(__DIR__.'/../../test-pages/regex-test.html'),
        statusCode: 200,
        headers: [],
    );

    $year = $page->filterCSS('h1')->regex('/\d{4}/');

    expect($year)->toBe('2024');
});
