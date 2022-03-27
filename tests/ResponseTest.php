<?php

declare(strict_types=1);

use Mockery\Mock;
use ScraPHP\Response;
use ScraPHP\HttpClient\HttpClientInterface;


test('deve conter url, httpClient e statusCode', function(){

    /** @var Mock|HttpClientInterface */
    $httpClient = Mockery::mock(HttpClientInterface::class);

    $response = new Response(
        url: 'http://example.com',
        httpClient: $httpClient,
        statusCode: 200
    );

    expect($response->statusCode())->toBe(200);
    expect($response->url())->toBe('http://example.com');
});

test('deve chamar filtro css do httpclient', function(){

    /** @var Mock|HttpClientInterface */
    $httpClient = Mockery::mock(HttpClientInterface::class);
    $httpClient->shouldReceive('css')->once()->with('.seletor')->andReturn(null);

    $response = new Response(
        url: 'http://example.com',
        httpClient: $httpClient,
        statusCode: 0
    );

    $value = $response->css('.seletor');

    expect($value)->toBeNull();
});

test('deve chamar cssEach do httpclient', function(){

    $closure = function($element){
        return [1,2,3];
    };

    /** @var Mock|HttpClientInterface */
    $httpClient = Mockery::mock(HttpClientInterface::class);
    $httpClient->shouldReceive('cssEach')->once()->with('.seletor', $closure)->andReturn([1,2,3]);

    $response = new Response(
        url: 'http://example.com',
        httpClient: $httpClient,
        statusCode: 0
    );

    $value = $response->cssEach('.seletor', $closure);

    expect($value)->toBe([1,2,3]);
});