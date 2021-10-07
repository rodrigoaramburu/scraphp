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
    );

    $value = $response->css('.seletor');

    expect($value)->toBeNull();
});

test('deve chamar  cssEach do httpclient', function(){

    $closure = function($ele){

    };

    $httpClient = $this->createMock(HttpClientInterface::class);
    $httpClient->expects( $this->once())->method('cssEach')->with('.seletor', $closure);

    $response = new Response(
        url: 'http://example.com',
        httpClient: $httpClient,
    );

    $value = $response->cssEach('.seletor', $closure);

    expect($value)->toBeNull();
});