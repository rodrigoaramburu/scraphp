<?php

declare(strict_types=1);

use Mockery\Mock;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use ScraPHP\Scrap;
use ScraPHP\Request;
use ScraPHP\Middleware\LogMiddleware;
use ScraPHP\ResponseInterface;

class ScrapTest extends Scrap{
    public function parse(ResponseInterface $response): Generator
    {
        yield []; 
    }
};

test('deve logar beforeAll e afterAll', function(){

    /** @var Mock|LoggerInterface */
    $loggerMock = Mockery::mock(LoggerInterface::class);
    $loggerMock->shouldReceive('info')->once()->with('Iniciando ScrapTest...' );
    $loggerMock->shouldReceive('info')->once()->with('Finalizando ScrapTest.');
    
    $scrap = new ScrapTest();

    $log = new LogMiddleware(logger: $loggerMock);

    $log->beforeAll($scrap);
    $log->afterAll($scrap);
});


test('deve logar beforeRequest e afterRequest', function(){

    /** @var Mock|LoggerInterface */
    $loggerMock = Mockery::mock(LoggerInterface::class);
    $loggerMock->shouldReceive('info')->once()->with('ScrapTest - Acessando: http://localhost/pagina1.php...');
    $loggerMock->shouldReceive('info')->once()->with('ScrapTest - Status Code: 200');
    
    $scrap = new ScrapTest();
    
    $requset = Request::create(url: 'http://localhost/pagina1.php');
    
    /** @var Mock|ResponseInterface */
    $responseMock = Mockery::mock(ResponseInterface::class);
    $responseMock->shouldReceive('statuscode')->andReturn(200);

    $log = new LogMiddleware(logger: $loggerMock);

    $log->beforeRequest($scrap, $requset);
    $log->afterRequest($scrap, $responseMock);
});



test('deve instanciar um logger ', function(){

    $log = new LogMiddleware();

    $logger = getPrivateAttr($log, 'logger');

    expect($logger)->toBeInstanceOf(Logger::class);
});


// function getPrivateAttr(object $obj, string $attr): mixed
// {
//     $refClass = new ReflectionClass( $obj );
//     $attr = $refClass->getProperty($attr);
//     $attr->setAccessible(true);
//     return $attr->getValue($obj);
// }