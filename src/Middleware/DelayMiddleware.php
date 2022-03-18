<?php

declare(strict_types=1);

namespace ScraPHP\Middleware;

use ScraPHP\Request;
use ScraPHP\Scrap;
use ScraPHP\Util\Clock;
use ScraPHP\Util\ClockInterface;

final class DelayMiddleware extends Middleware
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
