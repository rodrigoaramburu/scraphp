<?php

declare(strict_types=1);

namespace ScraPHP;

use Generator;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use ScraPHP\HttpClient\HttpClientException;
use ScraPHP\HttpClient\HttpClientInterface;
use ScraPHP\HttpClient\Simple\HttpClient;
use ScraPHP\HttpClient\WebDriver\HttpClientWebDriver;

final class Engine
{
    private array $scraps;
    private HttpClientInterface $httpClient;
    private Logger $logger;

    public function __construct(
        ?HttpClientInterface $httpClient = null,
        ?Logger $logger = null
    ) {
        $this->httpClient = $httpClient ?? new HttpClient();

        if ($logger === null) {
            $this->logger = new Logger('ScraPHP.Engine');
            $handler = new StreamHandler('php://stdout', Logger::DEBUG);
            $this->logger->pushHandler($handler);
        } else {
            $this->logger = $logger;
        }
    }

    public function scrap(Scrap $scrap): self
    {
        $this->scraps[] = $scrap;
        return $this;
    }

    public function scraps(): array
    {
        return $this->scraps;
    }

    public function useWebDriver(int $waitTimeAfterRequestSec = 0): self
    {
        $this->httpClient = new HttpClientWebDriver(
            waitTimeAfterRequestSec: $waitTimeAfterRequestSec,
        );

        return $this;
    }

    public function httpClient(): ?HttpClientInterface
    {
        return $this->httpClient;
    }

    public function start(): void
    {
        foreach ($this->scraps as $scrap) {
            $scrap->middlewareBeforeAll();
            $this->processScrap(scrap: $scrap);
            $scrap->middlewareAfterAll();
        }
    }

    private function processScrap(Scrap $scrap): void
    {
        while ($request = $scrap->nextRequest()) {
            try {
                $scrap->middlewareBeforeRequest($scrap, $request);
                $response = $this->httpClient->access(request: $request);
                $scrap->middlewareAfterRequest($scrap, $response);

                $generator = $scrap->parse(response: $response);

                $this->processWriters(generator: $generator, writers: $scrap->writers());
            } catch (HttpClientException $e) {
                $scrap->failRequest($request);
                $this->logger->error("Não foi possível acessar:  {$request->url()} - {$request->failCount()} fails");
            }
        }
    }

    private function processWriters(Generator $generator, array $writers): void
    {
        $generator->rewind();
        foreach ($generator as $data) {
            foreach ($writers as $writer) {
                $writer->data($data);
            }
        }
    }
}
