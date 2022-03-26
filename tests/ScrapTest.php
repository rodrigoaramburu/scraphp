<?php

declare(strict_types=1);

use ScraPHP\Scrap;
use ScraPHP\Request;
use ScraPHP\Response;
use ScraPHP\ResponseInterface;
use ScraPHP\Writers\WriterInterface;

test('Deve permitir adicionar uma request e recupearar', function(){

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


test('Deve permitir adicionar um writer e recupearar', function(){

    $writer1 = $this->createMock(WriterInterface::class);
    $writer2 = $this->createMock(WriterInterface::class);
    $scrap = new class extends Scrap{
        public function parse(ResponseInterface $response): Generator
        {
            yield []; 
        }
    };

    $scrap->addWriter($writer1);
    $scrap->addWriter($writer2);

    expect( count($scrap->writers() ) )->toBe(2);
    expect($scrap->writers()[0] )->toBe($writer1);
    expect($scrap->writers()[1] )->toBe($writer2);
    
});