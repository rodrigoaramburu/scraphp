<?php

declare(strict_types=1);

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use ScraPHP\HttpClient\FilteredElement;
use ScraPHP\HttpClient\WebDriver\WebDriverPage;

beforeEach(function () {
    $chromeOptions = new ChromeOptions();
    $chromeOptions->addArguments(['-headless']);

    $desiredCapabilities = DesiredCapabilities::chrome();
    $desiredCapabilities->setCapability(ChromeOptions::CAPABILITY, $chromeOptions);

    $this->webDriver = RemoteWebDriver::create('http://localhost:4444', $desiredCapabilities);

});

afterEach(function () {
    $this->webDriver->quit();
});

test('have attributes', function () {
    $this->webDriver->get('http://localhost:8000/hello-world.php');

    $page = new WebDriverPage(
        webDriver: $this->webDriver,
        statusCode: 200,
        headers: ['Content-Type' => ['text/html; charset=UTF-8']],
    );

    expect($page)->toBeInstanceOf(WebDriverPage::class)
        ->url()->toBe('http://localhost:8000/hello-world.php')
        ->statusCode()->toBe(200)
        ->htmlBody()->toContain('<title>Página Teste</title>', '<h1>Hello World</h1>')
        ->headers()->toBeArray()
        ->headers()->toBe(['Content-Type' => ['text/html; charset=UTF-8']])
        ->header('Content-Type')->toBe(['text/html; charset=UTF-8'])
        ->webDriver()->toBe($this->webDriver);

});

test('filter elements by tag name', function () {

    $this->webDriver->get('http://localhost:8000/seletors.html');

    $page = new WebDriverPage(
        statusCode: 200,
        headers: [],
        webDriver: $this->webDriver,
    );

    $text = $page->filterCSS('h1')->text();

    expect($text)->toBe('Teste Seletores Titulo');
});

test('filter elements by class', function () {

    $this->webDriver->get('http://localhost:8000/seletors.html');
    $page = new WebDriverPage(
        statusCode: 200,
        headers: [],
        webDriver: $this->webDriver,
    );

    $text = $page->filterCSS('.paragrafo')->text();

    expect($text)->toBe('Lorem ipsum dolor sit amet consectetur.');
});

test('get attribute from element', function () {
    $this->webDriver->get('http://localhost:8000/seletors.html');
    $page = new WebDriverPage(
        statusCode: 200,
        headers: [],
        webDriver: $this->webDriver,
    );

    $attr = $page->filterCSS('a')->attr('href');

    expect($attr)->toBe('https://www.google.com');
});

test('iterate filtered elements', function () {
    $this->webDriver->get('http://localhost:8000/seletors.html');
    $page = new WebDriverPage(
        statusCode: 200,
        headers: [],
        webDriver: $this->webDriver,
    );

    $result = $page->filterCSSEach('ul li', function (FilteredElement $element, int $i) {
        return $element->text().' - '.$i;
    });

    expect($result)->toBe(['Item 1 - 0', 'Item 2 - 1', 'Item 3 - 2']);
});

test('chain filterCSS', function () {
    $this->webDriver->get('http://localhost:8000/seletors.html');
    $page = new WebDriverPage(
        statusCode: 200,
        headers: [],
        webDriver: $this->webDriver,
    );

    $text = $page->filterCSS('ul')->filterCSS('li')->text();

    expect($text)->toBe('Item 1');
});

test('chain css filterCSS with filterCSSEach ', function () {
    $this->webDriver->get('http://localhost:8000/seletors.html');
    $page = new WebDriverPage(
        statusCode: 200,
        headers: [],
        webDriver: $this->webDriver,
    );

    $result = $page->filterCSS('ul')->filterCSSEach('li', function (FilteredElement $element, int $i) {
        return $element->text().' - '.$i;
    });

    expect($result)->toBe(['Item 1 - 0', 'Item 2 - 1', 'Item 3 - 2']);
});

test('chain css filterEach with filterCSS', function () {
    $this->webDriver->get('http://localhost:8000/seletors.html');
    $page = new WebDriverPage(
        statusCode: 200,
        headers: [],
        webDriver: $this->webDriver,
    );

    $result = $page->filterCSSEach('.data', function (FilteredElement $element, int $i) {
        return $element->filterCSS('.nome')->text();
    });

    expect($result)->toBe(['Anderson', 'Carlos', 'Rafael']);
});

test('filter with js', function () {
    $this->webDriver->get('http://localhost:8000/js-page.html');
    $page = new WebDriverPage(
        statusCode: 200,
        headers: [],
        webDriver: $this->webDriver,
    );

    $result = $page->filterCSS('#text')->text();

    expect($result)->toBe('Lorem ipsum dolor sit amet consectetur.');
});

test('return null if element not found', function () {

    $this->webDriver->get('http://localhost:8000/paragraph.html');
    $page = new WebDriverPage(
        statusCode: 200,
        headers: [],
        webDriver: $this->webDriver,
    );

    $result = $page->filterCSS('#not-found');

    expect($result)->toBeNull();
});

test('return null if element not found in chain', function () {

    $this->webDriver->get('http://localhost:8000/paragraph.html');
    $page = new WebDriverPage(
        statusCode: 200,
        headers: [],
        webDriver: $this->webDriver,
    );

    $result = $page->filterCSS('p')->filterCSS('.not-found');

    expect($result)->toBeNull();
});

test('return empty array iterating on not found filtered elements', function () {
    $this->webDriver->get('http://localhost:8000/seletors.html');
    $page = new WebDriverPage(
        statusCode: 200,
        headers: [],
        webDriver: $this->webDriver,
    );

    $result = $page->filterCSSEach('ul .not-found', function (FilteredElement $element, int $i) {
        return $element->text().' - '.$i;
    });

    expect($result)->toBe([]);
});


test('have a title', function () {

    $this->webDriver->get('http://localhost:8000/hello-world.php');
    $page = new WebDriverPage(
        statusCode: 200,
        headers: [],
        webDriver: $this->webDriver,
    );

    expect($page)->toBeInstanceOf(WebDriverPage::class)
        ->title()->toBe('Página Teste');

});
