<?php

declare(strict_types=1);

use Mockery\Mock;
use ScraPHP\Scrap;
use ScraPHP\Request;
use ScraPHP\Util\Clock;
use ScraPHP\Util\ClockInterface;
use ScraPHP\Middleware\DelayMiddleware;

test('deve executar delay', function(){

    /** @var Mock|Scrap */
    $scrap = Mockery::mock(Scrap::class);

    /** @var Mock|ClockInterface */
    $clockMock = Mockery::mock(ClockInterface::class);
    $clockMock->shouldReceive('delay')->with(10);

    $clock = new DelayMiddleware(secs: 10, clock: $clockMock);
    $clock->beforeRequest($scrap, Request::create(url: 'http://localhost/pagina1.php'));
});