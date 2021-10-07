<?php

declare(strict_types=1);

use ScraPHP\Writers\LogWriter;



test('deve escrever dados', function(){

    $fileLog = 'tests/tmp/logwriter.txt';
    $data = [ 'a' => 'b', 'c'=>'d'];
    $writer = new LogWriter(stream: $fileLog);

    $writer->data([ 'a' => 'b', 'c'=>'d']);

    $out = file_get_contents($fileLog);

    expect($out)->toContain( 'ScraPHP.INFO: '. json_encode($data) );

    unlink($fileLog);
});