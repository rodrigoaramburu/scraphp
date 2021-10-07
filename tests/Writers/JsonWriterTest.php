<?php

declare(strict_types=1);

use ScraPHP\Writers\LogWriter;
use ScraPHP\Writers\JsonWriter;



test('deve escrever dados', function(){

    $filejson = 'tests/tmp/jsonwriter';
    $data = [ 'a' => 'b', 'c'=>'d'];

    $writer = new JsonWriter(stream: $filejson);

    $writer->data([ 'a' => 'b', 'c'=>'d']);
    unset($writer);

    $out = file_get_contents($filejson);
    expect($out)->toBe( json_encode([$data]) );

    unlink($filejson);
});