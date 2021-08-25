<?php
declare(strict_types=1);

use ScraPHP\HttpClient\WebDriverProcess;

test('deve iniciar o processo do webdriver', function(){

    $webDriveProcess = new WebDriverProcess();
    $webDriveProcess->run();
    $pid = $webDriveProcess->pid();

    expect(file_exists("/proc/{$pid}"))->toBeTrue();
    $webDriveProcess->stop();
});


test('deve para o processo do webdriver', function(){
    $webDriveProcess = new WebDriverProcess();
    $webDriveProcess->run();

    $pid = $webDriveProcess->pid();

    $webDriveProcess->stop();

    expect(file_exists("/proc/{$pid}"))->toBeFalse();


});