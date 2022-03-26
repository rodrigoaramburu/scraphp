<?php

declare(strict_types=1);

use Mockery\Mock;
use ScraPHP\Request;
use ScraPHP\Response;
use ScraPHP\ResponseInterface;
use ScraPHP\Util\ClockInterface;
use ScraPHP\HttpClient\HttpClientException;
use ScraPHP\HttpClient\HttpClientElementInterface;
use ScraPHP\HttpClient\WebDriver\HttpClientWebDriver;

test('deve acessar uma página e devolver um response', function(){

    $httpClient = new HttpClientWebDriver();

    $response = $httpClient->access(Request::create(url: 'http://localhost:9666/page1.php'));

    expect($response)
        ->toBeInstanceOf(ResponseInterface::class)
        ->bodyHtml()
            ->toContain('<h1>Titulo 1</h1>','<h2>Titulo 2</h2>');
});


test('deve recuperar um nó de texto de um elemento através de um seletor CSS', function(){
    $httpClient = new HttpClientWebDriver();

    $httpClient->access( Request::create(url: 'http://localhost:9666/page1.php'));

    $text = $httpClient->css('h1')->text();
    expect($text)->toBe('Titulo 1');

});


test('deve recuperar o valor de um atributo de um elemento através de um seletor CSS', function(){
    $httpClient = new HttpClientWebDriver();

    $httpClient->access(Request::create(url: 'http://localhost:9666/page1.php'));

    $value = $httpClient->css('.teste')->attr('value');
    expect($value)->toBe('um teste');
});

test('deve percorrer vários elementos através de um seletor', function(){

    $httpClient = new HttpClientWebDriver();

    $httpClient->access(Request::create(url: 'http://localhost:9666/page1.php'));
    
    $expectTexts = $httpClient->cssEach('.item', function(HttpClientElementInterface $httpClientElement){
        return $httpClientElement->text();
    } );

    expect($expectTexts)->toBe(['Teste 1','Teste 2','Teste 3']);
});


test('deve percorrer os elementos filhos de um elemento',function(){
    $httpClient = new HttpClientWebDriver();

    $httpClient->access(Request::create(url: 'http://localhost:9666/page1.php'));
    
    $expectTexts = $httpClient->css('.lista')->each('li', function(HttpClientElementInterface $httpClientElement){
        return $httpClientElement->text();
    } );

    expect($expectTexts)->toBe(['Item 1','Item 2','Item 3','Item 4']);
});

test('deve pegar o html dentro de um seletor', function(){

    $httpClient = new HttpClientWebDriver();

    $httpClient->access(Request::create(url: 'http://localhost:9666/page1.php'));
    
    $html = trim( $httpClient->css('.html')->html() );
    expect($html)->toBe('<div>tag <strong>negrito</strong> outra</div>');
});


test('deve lancar exceção se não foi possível acessar a página',function(){
    $httpClient = new HttpClientWebDriver();

    $httpClient->access(Request::create(url: 'http://localhost:54321/page1.php'));
    
})->throws(HttpClientException::class, 'Erro ao acessar a página: ');


test('deve realizar uma requisição post', function(){

    $httpClient = new HttpClientWebDriver();

    $request = Request::create(url: 'http://localhost:9666/post.php')
            ->post()
            ->body(
                body: [
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



test('deve pegar o n elemento de um seletor', function(){

    $httpClient = new HttpClientWebDriver();

    $httpClient->access(Request::create(url: 'http://localhost:9666/page1.php'));
    
    $text = $httpClient->css('.lista li:nth-child(3)')->text();
    expect($text)->toBe('Item 3');
});


test('deve pegar permiter encadear filtros css', function(){

    $httpClient = new HttpClientWebDriver();

    $httpClient->access(Request::create(url: 'http://localhost:9666/page1.php'));
    
    $text = $httpClient->css('.lista')->css('li:nth-child(3)')->text();
    expect($text)->toBe('Item 3');
});



test('deve retornar null se o elemento não exitir', function(){

    $httpClient = new HttpClientWebDriver();

    $httpClient->access(Request::create(url: 'http://localhost:9666/page1.php'));
    
    $naoExiste = $httpClient->css('.nao-existe');
    expect($naoExiste)->toBeNull();

    $listaImg = $httpClient->css('.lista')->css('img');
    expect($listaImg)->toBeNull();
});



test('deve executar um delay após a requisição get ser realizada', function(){

    /** @var Mock|ClockInterface */
    $clockMock = Mockery::mock(ClockInterface::class);
    $httpClient = new HttpClientWebDriver(waitTimeAfterRequestSec: 5);
    $httpClient->changeClock($clockMock);

    $clockMock->shouldReceive('delay')->once()->with(5);

    $httpClient->access(Request::create(url: 'http://localhost:9666/page1.php'));

});


test('deve executar um delay após a requisição post ser realizada', function(){

    /** @var Mock|ClockInterface */
    $clockMock = Mockery::mock(ClockInterface::class);
    $httpClient = new HttpClientWebDriver(waitTimeAfterRequestSec: 5);
    $httpClient->changeClock($clockMock);

    $clockMock->shouldReceive('delay')->once()->with(5);

    $httpClient->access(
        Request::create(url: 'http://localhost:9666/post.php')
        ->post()
    );
});