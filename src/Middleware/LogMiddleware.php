<?php

declare(strict_types=1);

namespace ScraPHP\Middleware;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use ScraPHP\Request;
use ScraPHP\Response;
use ScraPHP\Scrap;

final class LogMiddleware extends Middleware
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
        $this->logger->info('Iniciando ' . $scrap::class . '...');
    }

    public function afterAll(Scrap $scrap): void
    {
        $this->logger->info('Finalizando ' . $scrap::class . '.');
    }

    public function beforeRequest(Scrap $scrap, Request $request): void
    {
        $scrapName = $scrap::class;
        $this->logger->info("{$scrapName} - Acessando: {$request->url()}...");
    }

    public function afterRequest(Scrap $scrap, Response $response): void
    {
        $scrapName = $scrap::class;
        $this
            ->logger
            ->info("{$scrapName} - Status Code:{$response->statusCode()}");
    }
}
