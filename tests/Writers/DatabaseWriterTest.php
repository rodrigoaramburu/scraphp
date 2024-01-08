<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;
use ScraPHP\Writers\DatabaseWriter;

beforeEach(function () {
    $this->pdo = new PDO('sqlite::memory:');
    $this->pdo->exec(<<< 'SQL'
        CREATE TABLE users (
            id INTEGER PRIMARY KEY,
            name TEXT,
            lastname TEXT,
            age integer
        )
        SQL);

    $this->logger = Mockery::mock(LoggerInterface::class);

    $this->writer = new DatabaseWriter(
        $this->pdo,
        'users'
    );
});

test('write a record to database', function () {

    $this->writer->write([
        'name' => 'Rodrigo',
        'lastname' => 'Aramburu',
        'age' => 25,
    ]);

    $pstm = $this->pdo->prepare('SELECT * FROM users');
    $pstm->execute();
    $users = $pstm->fetchAll(\PDO::FETCH_ASSOC);

    expect($users[0])->toEqual([
        'id' => 1,
        'name' => 'Rodrigo',
        'lastname' => 'Aramburu',
        'age' => 25,
    ]);
});

test('check if a record exists in database', function () {

    $this->writer->write([
        'name' => 'Rodrigo',
        'lastname' => 'Aramburu',
        'age' => 25,
    ]);
    $this->writer->write([
        'name' => 'Antonio',
        'lastname' => 'Silva',
        'age' => 53,
    ]);
    $this->writer->write([
        'name' => 'Gisele',
        'lastname' => 'Antunes',
        'age' => 15,
    ]);

    expect($this->writer->exists(['name' => 'Rodrigo']))->toBeTrue();
    expect($this->writer->exists(['name' => 'Jucelino']))->toBeFalse();
    expect($this->writer->exists(['lastname' => 'Silva']))->toBeTrue();
    expect($this->writer->exists(['lastname' => 'Antonio']))->toBeFalse();

});

test('check if a record exists in database with two criteria', function () {

    $this->writer->write([
        'name' => 'Rodrigo',
        'lastname' => 'Aramburu',
        'age' => 25,
    ]);
    $this->writer->write([
        'name' => 'Antonio',
        'lastname' => 'Silva',
        'age' => 53,
    ]);
    $this->writer->write([
        'name' => 'Gisele',
        'lastname' => 'Antunes',
        'age' => 15,
    ]);

    expect($this->writer->exists(['name' => 'Rodrigo', 'age' => 25]))->toBeTrue();
    expect($this->writer->exists(['name' => 'Rodrigo', 'age' => 42]))->toBeFalse();
});
