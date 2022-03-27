<?php

declare(strict_types=1);

use Mockery\Mock;
use ScraPHP\Scrap;
use ScraPHP\Request;
use ScraPHP\ResponseInterface;
use ScraPHP\Middleware\Middleware;
use ScraPHP\Writers\WriterInterface;

test('deve permitir adicionar uma request e recupera-lo', function(){

    $request1 = Request::create(url: 'http://test1.com');
    $request2 = Request::create(url: 'http://test2.com');
    $request3 = Request::create(url: 'http://test3.com');

    $scrap = new class extends Scrap{
        public function parse(ResponseInterface $response): Generator
        {
            yield []; 
        }
    };

    $scrap->addRequest($request1);
    $scrap->addRequest($request2);
    $scrap->addRequest($request3);

    expect($scrap->nextRequest() )->toBe($request1);
    expect($scrap->nextRequest() )->toBe($request2);
    expect($scrap->nextRequest() )->toBe($request3);

    expect($scrap->nextRequest() )->toBe(null);
});


test('deve permitir adicionar um writer e recupera-lo', function(){

    /** @var Mock|(WriterInterface */
    $writer1 = Mockery::mock(WriterInterface::class);

    /** @var Mock|(WriterInterface */
    $writer2 = Mockery::mock(WriterInterface::class);
    $scrap = new class extends Scrap{
        public function parse(ResponseInterface $response): Generator
        {
            yield []; 
        }
    };

    $scrap->withWriter($writer1);
    $scrap->withWriter($writer2);

    expect( $scrap->writers() )->toHaveCount(2);
    expect($scrap->writers()[0] )->toBe($writer1);
    expect($scrap->writers()[1] )->toBe($writer2);
    
});


test('deve permitir adicionar um middleware e recupera-lo', function(){
    
    /** @var Mock|Middleware */
    $middleware1 = Mockery::mock(Middleware::class);

    /** @var Mock|Middleware */
    $middleware2 = Mockery::mock(Middleware::class);
    $scrap = new class extends Scrap{
        public function parse(ResponseInterface $response): Generator
        {
            yield []; 
        }
    };

    $scrap->withMiddleware($middleware1);
    $scrap->withMiddleware($middleware2);

    expect( $scrap->middlewares() )->toHaveCount(2);
    expect($scrap->middlewares()[0] )->toBe($middleware1);
    expect($scrap->middlewares()[1] )->toBe($middleware2);
});


test('deve chamar beforeAll e afterAll de todos middlewares', function(){
     /** @var Mock|Middleware */
     $middleware1 = Mockery::mock(Middleware::class);

     /** @var Mock|Middleware */
     $middleware2 = Mockery::mock(Middleware::class);
     $scrap = new class extends Scrap{
         public function parse(ResponseInterface $response): Generator
         {
             yield []; 
         }
     };

     $middleware1->shouldReceive('beforeAll')->once();
     $middleware2->shouldReceive('beforeAll')->once();
     $middleware1->shouldReceive('afterAll')->once();
     $middleware2->shouldReceive('afterAll')->once();

     $scrap->withMiddleware($middleware1);
     $scrap->withMiddleware($middleware2);

     $scrap->middlewareBeforeAll();
     $scrap->middlewareAfterAll();
});



test('deve chamar beforeRequest e afterRequest de todos middlewares', function(){
     /** @var Mock|Middleware */
     $middleware1 = Mockery::mock(Middleware::class);
     
     /** @var Mock|Middleware */
     $middleware2 = Mockery::mock(Middleware::class);
     $scrap = new class extends Scrap{
        public function parse(ResponseInterface $response): Generator
        {
             yield []; 
        }
    };
        
    /** @var Mock|ResponseInterface */
    $response = Mockery::mock(ResponseInterface::class);
    
    $request = Request::create(url: 'http://localhost/page1.php');


     $middleware1->shouldReceive('beforeRequest')->once()->with($scrap, $request);
     $middleware2->shouldReceive('beforeRequest')->once()->with($scrap, $request);
     $middleware1->shouldReceive('afterRequest')->once()->with($scrap, $response);
     $middleware2->shouldReceive('afterRequest')->once()->with($scrap, $response);

     $scrap->withMiddleware($middleware1);
     $scrap->withMiddleware($middleware2);

     $scrap->middlewareBeforeRequest($scrap, $request);
     $scrap->middlewareAfterRequest($scrap, $response);
});



test('deve incrementar a contagem de falhas e adicionar novamente o request na fila do scrap', function(){

    $scrap = new class extends Scrap{
        public function parse(ResponseInterface $response): Generator
        {
             yield []; 
        }
    };

    $request = Request::create(url: 'http://localhost/page1.php');


    $scrap->failRequest($request);

    expect($request->failCount())->toBe(1);
    expect($scrap->nextRequest())->toBe($request);
});


test('não deve incrementar a contagem de falhas e adicionar novamente o request na fila do scrap se ultrapassar numero de retries', function(){

    $scrap = new class extends Scrap{
        public function parse(ResponseInterface $response): Generator
        {
             yield []; 
        }
    };

    $request = Request::create(url: 'http://localhost/page1.php');


    $scrap->failRequest($request);
    $scrap->nextRequest();
    $scrap->failRequest($request);
    $scrap->nextRequest();
    $scrap->failRequest($request);
    $scrap->nextRequest();
    $scrap->failRequest($request);

    expect($request->failCount())->toBe(4);
    expect($scrap->nextRequest())->toBe(null);
});