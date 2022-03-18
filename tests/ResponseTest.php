<?php

declare(strict_types=1);

use ScraPHP\Response;
use ScraPHP\HttpClient\HttpClientInterface;


test('deve chamar filtro css do httpclient', function(){

    $httpClient = $this->createMock(HttpClientInterface::class);
    $httpClient->expects( $this->once())->method('css')->with('.seletor')->willReturn(null);

    $response = new Response(
        url: 'http://example.com',
        httpClient: $httpClient,
        statusCode: 0
    );

    $value = $response->css('.seletor');

    expect($value)->toBeNull();
});

test('deve chamar  cssEach do httpclient', function(){

    $closure = function($element){
        return [1,2,3];
    };

    $httpClient = $this->createMock(HttpClientInterface::class);
    $httpClient->expects( $this->once())->method('cssEach')->with('.seletor', $closure)->willReturn([1,2,3]);

    $response = new Response(
        url: 'http://example.com',
        httpClient: $httpClient,
        statusCode: 0
    );

    $value = $response->cssEach('.seletor', $closure);

    expect($value)->toBe([1,2,3]);
});