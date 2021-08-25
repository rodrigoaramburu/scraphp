<?php

declare(strict_types=1);

namespace ScraPHP\HttpClient;

use Symfony\Component\Process\Process;

class WebDriverProcess
{

    private static string $chromeDriverPath = 'chromedriver'; 
    private Process $process; 

    public function __construct(){
        $this->process = new Process([self::$chromeDriverPath, '--port=4444']);
    }

    public function run(): void
    {
        $this->process->start();
        while(! str_contains( $this->process->getOutput(), 'was started successfully' )){
            sleep(1);
        }
    }

    public function stop(): void
    {
        $this->process->stop();
    }

    public function pid()
    {
        return $this->process->getPid();
    }

}