<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;
use ScraPHP\Writers\JsonWriter;

beforeEach(function () {
    /** @var LoggerInterface */
    $this->logger = Mockery::mock(LoggerInterface::class);
    $this->jsonWriter = new JsonWriter(__DIR__.'/../assets/test.json');
    $this->jsonWriter->withLogger($this->logger);
});

afterEach(function () {
    if (file_exists(__DIR__.'/../assets/test.json')) {
        unlink(__DIR__.'/../assets/test.json');
    }
});

test('write a json file', function () {

    $this->logger->shouldReceive('info')->once();
    $this->jsonWriter->write([
        'name' => 'Rodrigo',
        'lastname' => 'Aramburu',
        'age' => 25,
    ]);

    expect(__DIR__.'/../assets/test.json')->toBeFile();
    expect(json_decode(file_get_contents(__DIR__.'/../assets/test.json'), true))->toBe([
        [
            'name' => 'Rodrigo',
            'lastname' => 'Aramburu',
            'age' => 25,
        ],
    ]);
});

test('check if a value exists in the jsons file', function () {

    $this->logger->shouldReceive('info')->times(3);
    $this->jsonWriter->write([
        'name' => 'Rodrigo',
        'lastname' => 'Aramburu',
        'age' => 25,
    ]);
    $this->jsonWriter->write([
        'name' => 'Antonio',
        'lastname' => 'Silva',
        'age' => 53,
    ]);
    $this->jsonWriter->write([
        'name' => 'Gisele',
        'lastname' => 'Antunes',
        'age' => 15,
    ]);

    expect($this->jsonWriter->exists(['name' => 'Rodrigo']))->toBeTrue();
    expect($this->jsonWriter->exists(['name' => 'Jucelino']))->toBeFalse();
    expect($this->jsonWriter->exists(['lastname' => 'Silva']))->toBeTrue();
    expect($this->jsonWriter->exists(['lastname' => 'Antonio']))->toBeFalse();
});

test('check if a value exists in the json file with two criteria', function () {

    $this->logger->shouldReceive('info')->times(3);
    $this->jsonWriter->write([
        'name' => 'Rodrigo',
        'lastname' => 'Aramburu',
        'age' => 25,
    ]);
    $this->jsonWriter->write([
        'name' => 'Antonio',
        'lastname' => 'Silva',
        'age' => 53,
    ]);
    $this->jsonWriter->write([
        'name' => 'Gisele',
        'lastname' => 'Antunes',
        'age' => 15,
    ]);

    expect($this->jsonWriter->exists(['name' => 'Rodrigo', 'age' => 25]))->toBeTrue();
    expect($this->jsonWriter->exists(['name' => 'Rodrigo', 'age' => 42]))->toBeFalse();
});

test('add content to a existing json', function () {

    file_put_contents(__DIR__.'/../assets/test.json', json_encode([
        [
            'name' => 'Rodrigo',
            'lastname' => 'Aramburu',
            'age' => 25,
        ],
    ]));

    $this->logger->shouldReceive('info')->once();
    $this->jsonWriter->write([
        'name' => 'Antonio',
        'lastname' => 'Silva',
        'age' => 53,
    ]);

    $jsonData = json_decode(file_get_contents(__DIR__.'/../assets/test.json'), true);
    expect($jsonData)->toBe([
        [
            'name' => 'Rodrigo',
            'lastname' => 'Aramburu',
            'age' => 25,
        ],
        [
            'name' => 'Antonio',
            'lastname' => 'Silva',
            'age' => 53,
        ],
    ]);

});
