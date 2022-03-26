<?php

use Mockery\Mock;
use ScraPHP\Scrap;
use ScraPHP\Engine;
use ScraPHP\Request;
use Psr\Log\LoggerInterface;
use ScraPHP\ResponseInterface;
use ScraPHP\Middleware\Middleware;
use ScraPHP\Writers\WriterInterface;
use ScraPHP\HttpClient\HttpClientException;
use ScraPHP\HttpClient\HttpClientInterface;
use ScraPHP\HttpClient\WebDriver\HttpClientWebDriver;

function arrayAsGenerator(array $array): Generator
{
    foreach ($array as $item) {
        yield $item;
    }
}


test('deve adicionar scraps ao engine', function(){

    $engine = new Engine();

    $scrap1 = new class extends Scrap{
        public function parse(ResponseInterface $response): Generator{
            yield [];
        }
    };

    $scrap2 = new class extends Scrap{
        public function parse(ResponseInterface $response): Generator{
            yield [];
        }
    };

    $engine->scrap( $scrap1 );
    $engine->scrap( $scrap2 );

    expect($engine->scraps())->toBe([$scrap1,$scrap2]);

});


test('deve processar parse dos scraps ao executar start', function(){

    /** @var WriterInterface|Mock */
    $writer = Mockery::mock(WriterInterface::class);
    $writer->shouldReceive('data')->once()->with(['a']);
    $writer->shouldReceive('data')->once()->with(['b']);
    
    $scrap = new class extends Scrap{
        public function parse(ResponseInterface $response): Generator{
            yield FROM [['a'],['b']];
        }
    };
    $scrap->addWriter($writer);
    $scrap->addRequest(Request::create(url: 'http://example.com'));
    
    /** @var Mock|ResponseInterface */
    $response = Mockery::mock(ResponseInterface::class);

    /** @var Mock|HttpClientInterface */
    $httpClient = Mockery::mock(HttpClientInterface::class);
    $httpClient->shouldReceive('access')->once()->andReturn($response);

    $engine = new Engine(httpClient: $httpClient);
    $engine->scrap($scrap);
    $engine->start();

});


test('deve permitir usar webdriver', function(){
    $engine = new Engine();
    $engine->useWebDriver();
    
    expect($engine->httpClient())->toBeInstanceOf(HttpClientWebDriver::class);
});


test('deve tentar novamente se a página do request não for encontrada', function(){
    $request = Request::create(url: 'http://example.com');

    $scrap = new class extends Scrap{
        public function parse(ResponseInterface $response): Generator{
            yield [];
        }
    };

    $scrap->addRequest($request);

    /** @var Mock|HttpClientInterface */
    $httpClient = Mockery::mock(HttpClientInterface::class);
    $httpClient->shouldReceive('access')->with($request)->andThrow(new HttpClientException());
    
    /** @var Mock|LoggerInterface */
    $logger = Mockery::mock(LoggerInterface::class);
    $logger->shouldReceive('error')->times(6);

    $engine = new Engine(httpClient: $httpClient, logger: $logger);
    $engine->scrap($scrap)
            ->start();

    expect($scrap->retry())->toBe(3);
});


test('deve logar o erro gerado pelo HttpClient', function(){
    $request = Request::create(url: 'http://example.com');

    $scrap = new class extends Scrap{
        public function parse(ResponseInterface $response): Generator{
            yield [];
        }
    };

    $scrap->addRequest($request);

    /** @var Mock|HttpClientInterface */
    $httpClient = Mockery::mock(HttpClientInterface::class);
    $httpClient->shouldReceive('access')->with($request)->andThrow(new HttpClientException('Mensagem de Teste'));
    
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



test('deve chamar middlewares do scrap', function(){

    /** @var Mock|ResponseInterface */
    $response = Mockery::mock(ResponseInterface::class);

    /** @var Mock|HttpClientInterface */
    $httpClient = Mockery::mock(HttpClientInterface::class);
    $httpClient->shouldReceive('access')->twice()->andReturn($response);
    
    $scrap = new class extends Scrap{
        public function parse(ResponseInterface $response): Generator{
            yield FROM [['a'],['b']];
        }
    };
    $scrap->addRequest(Request::create(url: 'http://example.com'));
    $scrap->addRequest(Request::create(url: 'http://example.com'));

    /** @var Middleware|Mock */
    $middleware = Mockery::mock(Middleware::class);
    $middleware->shouldReceive('beforeAll')->once();
    $middleware->shouldReceive('afterAll')->once();
    $middleware->shouldReceive('beforeRequest')->twice();
    $middleware->shouldReceive('afterRequest')->twice();
    
    $scrap->middleware($middleware);

    
    $engine = new Engine(httpClient: $httpClient);
    $engine->scrap($scrap);
    $engine->start();
});



function getPrivateAttr(object $obj, string $attr): mixed
{
    $refClass = new ReflectionClass( $obj );
    $attr = $refClass->getProperty($attr);
    $attr->setAccessible(true);
    return $attr->getValue($obj);
}