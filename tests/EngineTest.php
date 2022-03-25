<?php

use Mockery\Mock;
use ScraPHP\Scrap;
use ScraPHP\Engine;
use ScraPHP\Request;
use ScraPHP\Response;
use Psr\Log\LoggerInterface;
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

test('deve permitir usar webdriver', function(){
    $engine = new Engine();
    $engine->useWebDriver();
    
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


test('deve logar o erro gerado pelo HttpClient', function(){
    $request = Request::create(url: 'http://example.com');

    $scrap = new class extends Scrap{
        public function parse(Response $response): Generator{
            yield [];
        }
    };

    $scrap->addRequest($request);

    $httpClient = $this->createMock(HttpClientInterface::class);
    $httpClient->method('access')->with($request)->will($this->throwException(new HttpClientException('Mensagem de Teste')));
    
    /** @var LoggerInterface|Mock */
    $loggerMock = Mockery::mock(LoggerInterface::class);
    $loggerMock->shouldReceive('error')->times(3)->with("httpclient: Mensagem de Teste");
    $loggerMock->shouldReceive('error')->once()->with('Não foi possível acessar:  http://example.com - 1 fails');
    $loggerMock->shouldReceive('error')->once()->with('Não foi possível acessar:  http://example.com - 2 fails');
    $loggerMock->shouldReceive('error')->once()->with('Não foi possível acessar:  http://example.com - 3 fails');

    $engine = new Engine(httpClient: $httpClient, logger: $loggerMock);
    $engine->scrap($scrap)
            ->start();
});


test('deve receber a url do webdriver por parâmetro', function(){

    

    $engine = new Engine();
    $engine->useWebDriver(webDriverUrl: 'http://localhost:5555');

    $httpClient = $engine->httpClient();
    $driver = getPrivateAttr($httpClient, 'driver');
    $executor = getPrivateAttr($driver, 'executor');
    $url = getPrivateAttr($executor, 'url');
    
    expect($url)->toBe('http://localhost:5555');
});


function getPrivateAttr(object $obj, string $attr): mixed
{
    $refClass = new ReflectionClass( $obj );
    $attr = $refClass->getProperty($attr);
    $attr->setAccessible(true);
    return $attr->getValue($obj);
}