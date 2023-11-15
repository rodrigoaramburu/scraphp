<?php

declare(strict_types=1);

use ScraPHP\HttpClient\Guzzle\GuzzleHttpClient;
use ScraPHP\Page;

test('retrive a webpage and return an object page', function () {

    $guzzleClient = new GuzzleHttpClient();

    $page = $guzzleClient->get('http://localhost:8000/hello-world.php');

    expect($page)->toBeInstanceOf(Page::class);
    expect($page->statusCode())->toBe(200);
    expect($page->url())->toBe('http://localhost:8000/hello-world.php');
    expect($page->headers('my-header'))->toContain(['teste']);
    expect($page->header('my-header'))->toBe(['teste']);

    expect($page->content())->toBe(<<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <title>Página Teste</title>
</head>
<body>
    <h1>Hello World</h1>
</body>
</html>
HTML);

});
