<?php

declare(strict_types=1);

namespace ScraPHP;

use Generator;
use ScraPHP\Writers\WriterInterface;

abstract class Scrap
{
    /* @var $requests Request[] */
    private array $requests = [];
    private array $writers = [];
    private int $retry = 3;
    private int $delay = 0;

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
        if ($request->failCount() < $this->retry()) {
            $this->addRequest($request);
        }
    }

    public function changeDelay(int $delay): void
    {
        $this->delay = $delay;
    }
    public function delay(): int
    {
        return $this->delay;
    }
}
