<?php

declare(strict_types=1);

use ScraPHP\HttpClient\FilteredElement;
use Facebook\WebDriver\Chrome\ChromeOptions;
use ScraPHP\Exceptions\InvalidLinkException;
use ScraPHP\Exceptions\InvalidImageException;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use ScraPHP\HttpClient\WebDriver\WebDriverPage;
use Facebook\WebDriver\Remote\DesiredCapabilities;

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



test('get a link data', function () {
    $this->webDriver->get('http://localhost:8000/folder1/folder2/link.html');
    $page = new WebDriverPage(
        statusCode: 200,
        headers: [],
        webDriver: $this->webDriver,
    );

    $link = $page->filterCSS('#link1')->link();

    expect($link)
        ->text()->toBe('Link 1')
        ->uri()->toBe('http://localhost:8000/folder1/folder2/page1.html?param1=value1&param2=value2')
        ->rawUri()->toBe('http://localhost:8000/folder1/folder2/page1.html?param1=value1&param2=value2')
        ->query()->toBe(['param1' => 'value1', 'param2' => 'value2']);
});

test('get a link uri without domain', function () {
    $this->webDriver->get('http://localhost:8000/folder1/folder2/link.html');
    $page = new WebDriverPage(
        statusCode: 200,
        headers: [],
        webDriver: $this->webDriver,
    );

    $link = $page->filterCSS('#link2')->link();

    expect($link)
        ->text()->toBe('Link 2')
        ->uri()->toBe('http://localhost:8000/folder1/folder2/page2.html');
});

test('get a link uri relative', function () {
    $this->webDriver->get('http://localhost:8000/folder1/folder2/link.html');
    $page = new WebDriverPage(
        statusCode: 200,
        headers: [],
        webDriver: $this->webDriver,
    );

    $link = $page->filterCSS('#link3')->link();

    expect($link)
        ->text()->toBe('Link 3')
        ->uri()->toBe('http://localhost:8000/folder1/folder2/page3.html');
});


test('throw exception if link is invalid', function () {

    $this->webDriver->get('http://localhost:8000/folder1/folder2/link.html');
    $page = new WebDriverPage(
        statusCode: 200,
        headers: [],
        webDriver: $this->webDriver,
    );

    $link = $page->filterCSS('#not-link')->link();

})->throws(InvalidLinkException::class);


test('get a image data', function () {
    $this->webDriver->get('http://localhost:8000/folder1/folder2/image.html');
    $page = new WebDriverPage(
        statusCode: 200,
        headers: [],
        webDriver: $this->webDriver,
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

    $this->webDriver->get('http://localhost:8000/folder1/folder2/image.html');
    $page = new WebDriverPage(
        statusCode: 200,
        headers: [],
        webDriver: $this->webDriver,
    );

    $link = $page->filterCSS('#not-image')->image();

})->throws(InvalidImageException::class);


test('click on a button and execute a js action', function () {

    $this->webDriver->get('http://localhost:8000/click-page-teste.html');
    $page = new WebDriverPage(
        statusCode: 200,
        headers: [],
        webDriver: $this->webDriver,
    );

    $page->click('#button');
    sleep(2);

    $result = $page->filterCSS('#saida')->text();

    expect($result)->toBe('Clicked!');

});

test('scroll to the end of the padge', function () {

    $this->webDriver->get('http://localhost:8000/scroll-test.html');
    $page = new WebDriverPage(
        statusCode: 200,
        headers: [],
        webDriver: $this->webDriver,
    );

    $page->scrollToEnd();
    sleep(5);

    $result = $this->webDriver->executeScript('return window.pageYOffset;');

    expect($result)->toBeGreaterThan(24000);

});


test('check if an element is displayed', function () {
    $this->webDriver->get('http://localhost:8000/no-display-test.html');
    $page = new WebDriverPage(
        statusCode: 200,
        headers: [],
        webDriver: $this->webDriver,
    );

    expect($page->filterCSS('.present')->isDisplayed())->toBeTrue();
    expect($page->filterCSS('.no-display-1')->isDisplayed())->toBeFalse();
    expect($page->filterCSS('.no-display-2')->isDisplayed())->toBeFalse();
    expect($page->filterCSS('.no-display-3')->isDisplayed())->toBeFalse();
});


test('get a text by regex', function () {

    $this->webDriver->get('http://localhost:8000/regex-test.html');
    $page = new WebDriverPage(
        statusCode: 200,
        headers: [],
        webDriver: $this->webDriver,
    );

    $year = $page->filterCSS('h1')->regex('/\d{4}/');

    expect($year)->toBe('2024');
});


test('get a text by regex with group', function () {

    $this->webDriver->get('http://localhost:8000/regex-test.html');
    $page = new WebDriverPage(
        statusCode: 200,
        headers: [],
        webDriver: $this->webDriver,
    );

    $year = $page->filterCSS('p')->regex('/The year: (?<year>\d{4})/', 'year');

    expect($year)->toBe('2024');
});
