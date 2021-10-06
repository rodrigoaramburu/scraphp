<?php

declare(strict_types=1);

namespace ScraPHP;

use Generator;

abstract class Scrap
{
    private array $requests = [];

    abstract public function parse(Response $response): Generator;

    public function addRequest(Request $request): void
    {
        $this->requests[] = $request;
    }

    public function nextRequest(): ?Request
    {
        return array_shift($this->requests);
    }
}
