<?php

use ScraPHP\Scrap;
use ScraPHP\Engine;
use ScraPHP\Request;
use ScraPHP\Response;
use ScraPHP\Writers\WriterInterface;

function arrayAsGenerator(array $array)
{
    foreach ($array as $item) {
        yield $item;
    }
}


test('Deve adicionar um scrap', function(){

    $engine = new Engine();

    $scrap1 = new class extends Scrap{
        public function parse(Response $response): Generator{
            yield [];
        }
    };

    $scrap2 = new class extends Scrap{
        public function parse(Response $response): Generator{
            yield [];
        }
    };

    $engine->scrap( $scrap1 );
    $engine->scrap( $scrap2 );

    expect($engine->scraps())->toBe([$scrap1,$scrap2]);

});


test('deve processar um scrap', function(){

    $writer =  $this->createMock(WriterInterface::class);
    $writer->expects( $this->exactly(2) )->method('data');

    $scrap = $this->createMock(Scrap::class);
    $scrap->expects($this->exactly(2))->method('nextRequest')->willReturn( new Request(url: 'http://example.com'), null);
    $scrap->expects($this->once())->method('parse')->willReturn( arrayAsGenerator([ ['a'],['b']]));
    $scrap->expects($this->once())->method('writers')->willReturn([$writer]);
    
    $engine = new Engine();
    $engine->scrap($scrap);
    $engine->start();

});