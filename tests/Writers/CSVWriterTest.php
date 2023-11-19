<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;
use ScraPHP\Writers\CSVWriter;

beforeEach(function () {
    $this->logger = Mockery::mock(LoggerInterface::class);
    $this->writer = new CSVWriter(__DIR__.'/../assets/test.csv', ['name', 'lastname', 'age']);
    $this->writer->withLogger($this->logger);
});

afterEach(function () {
    if (file_exists(__DIR__.'/../assets/test.csv')) {
        unlink(__DIR__.'/../assets/test.csv');
    }
});

test('write csv line', function () {
    $this->logger->shouldReceive('info')->once();

    $this->writer->write([
        'name' => 'Rodrigo',
        'lastname' => 'Aramburu',
        'age' => 25,
    ]);

    expect(__DIR__.'/../assets/test.csv')->toBeFile();
    $file = file(__DIR__.'/../assets/test.csv');
    expect($file[0])->toBe("name,lastname,age\n");
    expect($file[1])->toBe('Rodrigo,Aramburu,25');
});

test('write csv line with unsorted keys ', function () {
    $this->logger->shouldReceive('info')->once();
    $this->writer->write([
        'lastname' => 'Aramburu',
        'age' => 25,
        'name' => 'Rodrigo',
    ]);

    expect(__DIR__.'/../assets/test.csv')->toBeFile();
    $file = file(__DIR__.'/../assets/test.csv');
    expect($file[0])->toBe("name,lastname,age\n");
    expect($file[1])->toBe('Rodrigo,Aramburu,25');
});

test('check if a value exists in the csv file', function () {

    $this->logger->shouldReceive('info')->times(3);

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

test('check if a value exists in the csv file with two criteria', function () {

    $this->logger->shouldReceive('info')->times(3);

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

test('add content to a existing cvs', function () {

    file_put_contents(__DIR__.'/../assets/test.csv', "name,lastname,age\nRodrigo,Aramburu,25");

    $this->logger = Mockery::mock(LoggerInterface::class);
    $this->writer = new CSVWriter(__DIR__.'/../assets/test.csv', ['name', 'lastname', 'age']);
    $this->writer->withLogger($this->logger);

    $this->logger->shouldReceive('info')->once();
    $this->writer->write([
        'name' => 'Antonio',
        'lastname' => 'Silva',
        'age' => 53,
    ]);

    $file = file(__DIR__.'/../assets/test.csv');
    expect($file[0])->toBe("name,lastname,age\n");
    expect($file[1])->toBe("Rodrigo,Aramburu,25\n");
    expect($file[2])->toBe('Antonio,Silva,53');

});

test('throw exception if file alread exits with different header', function () {
    file_put_contents(__DIR__.'/../assets/test.csv', "a1,s2,d3\nRodrigo,Aramburu,25");

    new CSVWriter(__DIR__.'/../assets/test.csv', ['name', 'lastname', 'age']);

})->throws(Exception::class, 'File '.__DIR__.'/../assets/test.csv'.' already exists with different header');


test('write a recorde without a header', function () {
    unlink(__DIR__.'/../assets/test.csv');

    $this->logger->shouldReceive('info')->once();
    $writer = new CSVWriter(__DIR__.'/../assets/test.csv', );
    $writer->withLogger($this->logger);

    $writer->write([
        'name' => 'Rodrigo',
        'lastname' => 'Aramburu',
        'age' => 25,
    ]);

    expect(file(__DIR__.'/../assets/test.csv'))->toBe(["\n", 'Rodrigo,Aramburu,25']);

});


test('ignore blank lines in exists method', function () {


    $fp = fopen(__DIR__.'/../assets/test.csv', 'a');
    fwrite($fp, "\n");
    fwrite($fp, "Antonio,Silva,53\n");
    fwrite($fp, "\n");
    fwrite($fp, "Gisele,Antunes,15");

    expect($this->writer->exists(['name' => 'Antonio']))->toBeTrue();
    expect($this->writer->exists(['name' => 'Gisele']))->toBeTrue();

});
