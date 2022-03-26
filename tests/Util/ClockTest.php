<?php

declare(strict_types=1);

use ScraPHP\Util\Clock;

test('deve executar delay', function(){

    $clock = new Clock();
    
    $timeBefore = time();
    $clock->delay(2);
    $timeAfter = time();

    $diff = $timeAfter - $timeBefore;
    expect($diff)->toBeGreaterThanOrEqual(2);
});