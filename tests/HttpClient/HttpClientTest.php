<?php 

declare(strict_types=1);

use ScraPHP\Request;
use ScraPHP\Response;
use ScraPHP\HttpClient\Simple\HttpClient;
use Symfony\Component\Process\Process;
use ScraPHP\HttpClient\HttpClientElementInterface;

$httpServerProcess = null;

beforeAll( function() use(&$httpServerProcess){
    $httpServerProcess = new Process(['php', '-S' ,'localhost:9666', '-t', 'tests/pages/']);
    $httpServerProcess->start();
});

afterAll(function() use(&$httpServerProcess) {
    $httpServerProcess->stop();
});

test('deve acessar uma página e devolver um response', function(){

    $httpClient = new HttpClient();

    $response = $httpClient->access( new Request(url: 'http://localhost:9666/page1.php'));

    expect($response)
        ->toBeInstanceOf(Response::class)
        ->bodyHtml()
            ->toContain('<h1>Titulo 1</h1>','<h2>Titulo 2</h2>');
});


test('deve recuperar um nó de texto de um elemento através de um seletor CSS', function(){
    $httpClient = new HttpClient();

    $httpClient->access( new Request(url: 'http://localhost:9666/page1.php'));

    expect($httpClient->css('h1')->text())->toBe('Titulo 1');
});


test('deve recuperar o valor de um atributo de um elemento através de um seletor CSS', function(){
    $httpClient = new HttpClient();

    $httpClient->access( new Request(url: 'http://localhost:9666/page1.php'));

    expect($httpClient->css('.teste')->attr('value'))->toBe('um teste');
});

test('deve percorrer vários elementos através de um seletor', function(){

    $httpClient = new HttpClient();

    $httpClient->access( new Request(url: 'http://localhost:9666/page1.php'));
    
    $expectTexts = [];

    $httpClient->cssEach('.item', function(HttpClientElementInterface $httpClientElement) use(&$expectTexts){
        $expectTexts[] = $httpClientElement->text();
    } );

    expect($expectTexts)->toBe(['Teste 1','Teste 2','Teste 3']);
});



test('deve percorrer os elementos filhos ',function(){
    $httpClient = new HttpClient();

    $httpClient->access( new Request(url: 'http://localhost:9666/page1.php'));
    
    $expectTexts = [];

    $httpClient->css('.lista')->each('li', function(HttpClientElementInterface $httpClientElement) use(&$expectTexts){
        $expectTexts[] = $httpClientElement->text();
    } );

    expect($expectTexts)->toBe(['Item 1','Item 2','Item 3','Item 4']);
});