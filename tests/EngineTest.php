<?php

use ScraPHP\Scrap;
use ScraPHP\Engine;
use ScraPHP\Request;
use ScraPHP\Response;
use ScraPHP\Util\Clock;
use ScraPHP\Util\ClockInterface;
use ScraPHP\Writers\WriterInterface;
use ScraPHP\HttpClient\HttpClientException;
use ScraPHP\HttpClient\HttpClientInterface;
use ScraPHP\HttpClient\WebDriver\HttpClientWebDriver;

function arrayAsGenerator(array $array)
{
    foreach ($array as $item) {
        yield $item;
    }
}


test('deve adicionar um scrap', function(){

    $engine = new Engine();

    $scrap1 = new class extends Scrap{
        public function parse(Response $response): Generator{
            yield [];
        }
    };

    $scrap2 = new class extends Scrap{
        public function parse(Response $response): Generator{
            yield [];
        }
    };

    $engine->scrap( $scrap1 );
    $engine->scrap( $scrap2 );

    expect($engine->scraps())->toBe([$scrap1,$scrap2]);

});


test('deve processar um scrap', function(){

    $writer =  $this->createMock(WriterInterface::class);
    $writer->expects( $this->exactly(2) )->method('data');

    $scrap = $this->createMock(Scrap::class);
    $scrap->expects($this->exactly(2))->method('nextRequest')->willReturn(Request::create(url: 'http://example.com'), null);
    $scrap->expects($this->once())->method('parse')->willReturn( arrayAsGenerator([ ['a'],['b']]));
    $scrap->expects($this->once())->method('writers')->willReturn([$writer]);
    
    $engine = new Engine();
    $engine->scrap($scrap);
    $engine->start();

});

test('deve permitir usar httpwebdriver', function(){
    $engine = new Engine();
    try{
        $engine->useWebDriver();
    }catch(Exception $e){
        echo $e->getMessage();
    }

    expect($engine->httpClient())->toBeInstanceOf(HttpClientWebDriver::class);
});


test('deve tentar novamente se request não encontrado', function(){
    $request = Request::create(url: 'http://example.com');

    $scrap = new class extends Scrap{
        public function parse(Response $response): Generator{
            yield [];
        }
    };

    $scrap->addRequest($request);

    $httpClient = $this->createMock(HttpClientInterface::class);
    $httpClient->method('access')->with($request)->will($this->throwException(new HttpClientException()));
    
    $engine = new Engine(httpClient: $httpClient);
    $engine->scrap($scrap)
            ->start();

    expect($scrap->retry())->toBe(3);
});

