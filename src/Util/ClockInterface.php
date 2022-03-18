<?php

declare(strict_types=1);

namespace ScraPHP\Util;

interface ClockInterface
{
    public function delay(int $secs): void;
}
