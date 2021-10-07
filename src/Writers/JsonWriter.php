<?php

declare(strict_types=1);

namespace ScraPHP\Writers;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use ScraPHP\Writers\WriterInterface;

final class JsonWriter implements WriterInterface
{

    private $file;

    public function __construct(string $stream = 'php://stdout')
    {
        $this->file = fopen($stream, 'w');
        fwrite($this->file,'[');
    }

    public function __destruct()
    {
        $position = ftell($this->file);
        fseek($this->file, $position-1);

        fwrite($this->file,']');
        fclose($this->file);
    }

    public function data(array $data): void
    {
        fwrite($this->file, json_encode($data, JSON_UNESCAPED_UNICODE). ',');
    }
} 