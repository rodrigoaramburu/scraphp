<?php

declare(strict_types=1);

use Mockery\Mock;
use ScraPHP\Writers\DatabaseWriter;

test('deve escrever dados', function(){
    
    /** @var Mock */
    $stmtMock = Mockery::mock(PDOStatement::class);
    $stmtMock->shouldReceive('bindValue')->with(':campo1','valor1');
    $stmtMock->shouldReceive('bindValue')->with(':campo2','valor2');
    $stmtMock->shouldReceive('bindValue')->with(':campo3','valor3');
    $stmtMock->shouldReceive('execute')->with();

    /** @var PDO|Mock */
    $pdoMock = Mockery::mock(PDO::class);

    $pdoMock->shouldReceive('prepare')->with("INSERT INTO tabela(campo1,campo2,campo3) VALUES(:campo1,:campo2,:campo3)")->andReturn($stmtMock);

    $databaseWriter = new DatabaseWriter($pdoMock, 'tabela');

    $databaseWriter->data([
        'campo1'=>'valor1',
        'campo2'=>'valor2',
        'campo3'=>'valor3',
    ]);

});