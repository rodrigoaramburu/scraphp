<?php 

declare(strict_types=1);


namespace ScraPHP\Middleware;

use ScraPHP\Scrap;
use Monolog\Logger;
use ScraPHP\Request;
use ScraPHP\Response;
use Monolog\Handler\StreamHandler;

class LogMiddleware extends Middleware
{

    private Logger $logger;

    public function __construct()
    {
        $this->logger = new Logger('ScraPHP.Engine');
        $handler = new StreamHandler('php://stdout', Logger::DEBUG);
        $this->logger->pushHandler($handler);
    }

    public function beforeAll(Scrap $scrap): void
    {
        $this->logger->info("Iniciando " . get_class($scrap) . "...");
    }
    
    public function afterAll(Scrap $scrap): void
    {
        $this->logger->info("Finalizando " . get_class($scrap) . ".");
    }
    
    public function beforeRequest(Scrap $scrap, Request $request): void
    {
        $scrapName = get_class($scrap);
        $this->logger->info("{$scrapName} - Acessando: {$request->url()}...");
        
    }
    
    public function afterRequest(Scrap $scrap, Response $response): void
    {
        $scrapName = get_class($scrap);
        $this->logger->info("{$scrapName} - Status Code:{$response->statusCode()}");
        
    }
}
