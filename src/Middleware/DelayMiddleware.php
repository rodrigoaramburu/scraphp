<?php 

declare(strict_types=1);


namespace ScraPHP\Middleware;

use ScraPHP\Scrap;
use Monolog\Logger;
use ScraPHP\Request;
use ScraPHP\Response;
use ScraPHP\Util\Clock;
use ScraPHP\Util\ClockInterface;
use Monolog\Handler\StreamHandler;

class DelayMiddleware extends Middleware
{

    private ClockInterface $clock;
    private int $secs;

    public function __construct(int $secs, ?ClockInterface $clock = null)
    {
        $this->clock = $clock ?? new Clock();
        $this->secs = $secs;
    }

    public function beforeRequest(Scrap $scrap, Request $request): void
    {
        $this->clock->delay($this->secs);
    }

}
