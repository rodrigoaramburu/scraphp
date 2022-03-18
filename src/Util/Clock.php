<?php

declare(strict_types=1);

namespace ScraPHP\Util;

final class Clock implements ClockInterface
{
    public function delay(int $secs): void
    {
        if ($secs === 0) {
            return;
        }
        sleep($secs);
    }
}
