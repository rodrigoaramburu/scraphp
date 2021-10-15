<?php

declare(strict_types=1);

namespace ScraPHP;

use ScraPHP\Scrap;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use ScraPHP\HttpClient\Simple\HttpClient;
use ScraPHP\HttpClient\HttpClientException;
use ScraPHP\HttpClient\HttpClientInterface;
use ScraPHP\HttpClient\WebDriver\WebDriverProcess;
use ScraPHP\HttpClient\WebDriver\HttpClientWebDriver;

final class Engine
{
    private array $scraps;
    private HttpClientInterface $httpClient; 
    private WebDriverProcess $webDriverProcess;
    private Logger $logger;

    public function __construct()
    {
        $this->httpClient = new HttpClient();
        $this->logger = new Logger('ScraPHP.Engine');
        $handler = new StreamHandler('php://stdout', Logger::DEBUG);
        $this->logger->pushHandler($handler);
    }
    
    public function scrap(Scrap $scrap): void
    {
        $this->scraps[] = $scrap;
    }

    public function scraps(): array
    {
        return $this->scraps;
    }

    public function useWebDriver(): void
    {
        $this->webDriverProcess = new WebDriverProcess();
        $this->webDriverProcess->run();

        $this->httpClient = new HttpClientWebDriver();
    }

    public function httpClient(): HttpClientInterface
    {
        return $this->httpClient;
    }
    public function setHttpClient(HttpClientInterface $httpClient): void
    {
        $this->httpClient = $httpClient;
    }

    public function start(): void
    {
        foreach($this->scraps as $scrap){
            $this->processScrap(scrap: $scrap);
        }
    }

    private function processScrap(Scrap $scrap): void
    {
        while($request = $scrap->nextRequest() ){
            try{
                $response = $this->httpClient->access(request: $request);
                $generator = $scrap->parse(response: $response);
                $generator->rewind();
                $writers = $scrap->writers();
                foreach ($generator as $data) {
                    foreach($writers as $writer){
                        $writer->data($data);
                    }
                }
            }catch(HttpClientException $e){
                $scrap->failRequest($request);
                $this->logger->error("Não foi possível acessar:  {$request->url()} - {$request->failCount()} fails");
            }
        }
     
    }
}