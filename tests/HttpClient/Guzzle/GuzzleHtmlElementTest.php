<?php


use ScraPHP\HttpClient\HtmlElement;
use Symfony\Component\DomCrawler\Crawler;
use ScraPHP\HttpClient\Guzzle\GuzzleHtmlElement;

test('return text of element', function () {

    $html = <<<HTML
    <html>
        <body>
            <h1>Hello World</h1>
        </body>
    </html>
    HTML;

    $element = new GuzzleHtmlElement(new Crawler($html));

    $text = $element->filterCSS('h1')->text();

    expect($text)->toBe('Hello World');
});

test('return an attribute of element', function () {

    $html = <<<HTML
    <html>
        <body>
            <h1>Hello World</h1>
            <a href="https://www.google.com">Google</a>
        </body>
    </html>
    HTML;

    $element = new GuzzleHtmlElement(new Crawler($html));

    $href = $element->filterCSS('a')->attr('href');

    expect($href)->toBe('https://www.google.com');
});


test('return null if element not found', function () {

    $html = <<<HTML
    <html>
        <body>
            <h1>Hello World</h1>
        </body>
    </html>
    HTML;

    $element = new GuzzleHtmlElement(new Crawler($html));

    $elementFiltered = $element->filterCSS('.not-found');

    expect($elementFiltered)->toBeNull();
});


test('iterate over elements', function () {

    $html = <<<HTML
    <html>
        <body>
            <h1>Hello World</h1>
            <ul>
                <li>Item 1</li>
                <li>Item 2</li>
                <li>Item 3</li>
            </ul>
        </body>
    </html>
    HTML;

    $element = new GuzzleHtmlElement(new Crawler($html));

    $result = $element->filterCSSEach('ul li', function (HtmlElement $element, int $i) {
        return $element->text('href') . ' - ' . $i;
    });

    expect($result)->toBe(['Item 1 - 0','Item 2 - 1','Item 3 - 2',]);
});
