<?php

declare(strict_types=1);

namespace ScraPHP;

use ScraPHP\Scrap;
use ScraPHP\HttpClient\Simple\HttpClient;

final class Engine
{
    private array $scraps;

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