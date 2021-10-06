<?php 

declare(strict_types=1);

use ScraPHP\Request;
use ScraPHP\Response;
use Symfony\Component\Process\Process;
use ScraPHP\HttpClient\Simple\HttpClient;
use ScraPHP\HttpClient\HttpClientException;
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


test('deve pegar o html dentro de um seletor', function(){

    $httpClient = new HttpClient();

    $httpClient->access( new Request(url: 'http://localhost:9666/page1.php'));
    
    expect(trim($httpClient->css('.html')->html()) )->toBe('<div>tag <strong>negrito</strong> outra</div>');
});



test('deve lancar exceção se não foi possível acessar a página',function(){
    $httpClient = new HttpClient();

    $httpClient->access( new Request(url: 'http://localhost:54321/page1.php'));
    
})->throws(HttpClientException::class, 'Erro ao acessar a página: ');


test('deve realizar uma requisição post', function(){

    $httpClient = new HttpClient();

    $request = new Request(
        url: 'http://localhost:9666/post.php',
        method: Request::POST,
        data: [
            'nome' => 'Joao',
            'sobrenome' => 'Silva',
            'email' => 'joaosilva@gmail.com',
            'senha' => '123456'
        ]
    );

    $httpClient->access( $request );

    expect($httpClient->bodyHtml())
        ->toContain('<div>Nome: Joao</div>')
        ->toContain('<div>Sobrenome: Silva</div>')
        ->toContain('<div>E-mail: joaosilva@gmail.com</div>')
        ->toContain('<div>Senha: 123456</div>');
});