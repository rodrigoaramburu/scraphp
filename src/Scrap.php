<?php

declare(strict_types=1);

namespace ScraPHP;

use Generator;
use ScraPHP\Writers\WriterInterface;

abstract class Scrap
{
    private array $requests = [];
    private array $writers = [];
    private int $retry = 3;
    abstract public function parse(Response $response): Generator;

    public function addRequest(Request $request): void
    {
        $this->requests[] = $request;
    }

    public function nextRequest(): ?Request
    {
        return array_shift($this->requests);
    }

    public function addWriter(WriterInterface $writer): void
    {
        $this->writers[] = $writer;
    }

    public function writers(): array
    {
        return $this->writers;
    }

    public function retry(): int
    {
        return $this->retry;
    }

    public function failRequest(Request $request): void
    {
        $request->failCountIncrement();
        if( $request->failCount() < $this->retry()){
            $this->addRequest($request);
        }
    }
}
