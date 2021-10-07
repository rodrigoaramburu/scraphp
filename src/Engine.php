<?php

declare(strict_types=1);

namespace ScraPHP;

use ScraPHP\Scrap;
use ScraPHP\HttpClient\Simple\HttpClient;
use ScraPHP\HttpClient\WebDriver\WebDriverProcess;
use ScraPHP\HttpClient\WebDriver\HttpClientWebDriver;

final class Engine
{
    private array $scraps;
    private WebDriverProcess $webDriverProcess;

    public function __construct()
    {
        $this->httpClient = new HttpClient();
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

    public function start(): void
    {
        foreach($this->scraps as $scrap){
            $this->processScrap(scrap: $scrap);
        }
    }

    private function processScrap(Scrap $scrap): void
    {
        while($request = $scrap->nextRequest() ){
            $response = $this->httpClient->access(request: $request);
            $generator = $scrap->parse(response: $response);
            $generator->rewind();
            $writers = $scrap->writers();
            foreach ($generator as $data) {
                foreach($writers as $writer){
                    $writer->data($data);
                }
            }
        }
     
    }
}