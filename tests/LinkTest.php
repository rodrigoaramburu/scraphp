<?php


declare(strict_types=1);

use ScraPHP\Link;

test('instanciate Link', function () {


    $link = new Link(
        text: 'Link Examplo',
        rawUri: 'https://example.com/hello-world.html',
        baseUri: 'https://example.com',
    );

    expect($link)
        ->text()->toBe('Link Examplo')
        ->uri()->toBe('https://example.com/hello-world.html')
        ->rawUri()->toBe('https://example.com/hello-world.html')
        ->query()->toBe([]);

});

test('get uri without domain', function () {
    $link = new Link(
        text: 'Link Examplo',
        rawUri: '/sections/posts/hello-world.html',
        baseUri: 'https://example.com/index.html',
    );

    expect($link->uri())->toBe('https://example.com/sections/posts/hello-world.html');

});


test('get uri with a relative uri', function () {
    $link = new Link(
        text: 'Link Examplo',
        rawUri: 'hello-world.html',
        baseUri: 'https://example.com/sections/posts/index.html',
    );

    expect($link->uri())->toBe('https://example.com/sections/posts/hello-world.html');

});

test('get params from a link', function () {
    $link = new Link(
        text: 'Link Examplo',
        rawUri: 'hello-world.html?param1=value1&param2=value2',
        baseUri: 'https://example.com/sections/posts/index.html',
    );

    expect($link->query())->toBe(['param1' => 'value1', 'param2' => 'value2']);
});
