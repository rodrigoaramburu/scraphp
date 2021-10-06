<?php

declare(strict_types=1);

use ScraPHP\Scrap;
use ScraPHP\Request;
use ScraPHP\Response;

test('Deve permitir adicionar uma request e recupearar', function(){

    $request1 = new Request(url: 'http://test1.com');
    $request2 = new Request(url: 'http://test2.com');
    $request3 = new Request(url: 'http://test3.com');

    $scrap = new class extends Scrap{
        public function parse(Response $response): Generator
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