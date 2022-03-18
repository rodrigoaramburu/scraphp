<?php

declare(strict_types=1);

namespace ScraPHP;

use Generator;
use ScraPHP\Middleware\Middleware;
use ScraPHP\Writers\WriterInterface;

abstract class Scrap
{
    /**
     * @var array<Request>
     */
    private array $requests = [];

    /**
     * @var array<WriterInterface>
     */
    private array $writers = [];
    private int $retry = 3;

    /**
     * @var array<Middleware>
     */
    private array $middlewares = [];

    abstract public function parse(Response $response): Generator;

    public function addRequest(Request $request): self
    {
        $this->requests[] = $request;
        return $this;
    }

    public function nextRequest(): ?Request
    {
        return array_shift($this->requests);
    }

    public function addWriter(WriterInterface $writer): self
    {
        $this->writers[] = $writer;
        return $this;
    }

    /**
     * @return array<WriterInterface>
     */
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

    public function middleware(Middleware $middleware): self
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

    /**
     * @return array<Middleware>
     */
    public function middlewares(): array
    {
        return $this->middlewares;
    }

    public function middlewareBeforeAll(): void
    {
        foreach ($this->middlewares as $middleware) {
            $middleware->beforeAll(scrap: $this);
        }
    }

    public function middlewareAfterAll(): void
    {
        foreach ($this->middlewares as $middleware) {
            $middleware->afterAll(scrap: $this);
        }
    }

    public function middlewareBeforeRequest(Scrap $scrap, Request $request): void
    {
        foreach ($this->middlewares as $key => $middleware) {
            $middleware->beforeRequest(scrap: $scrap, request: $request);
        }
    }

    public function middlewareAfterRequest(Scrap $scrap, Response $response): void
    {
        foreach ($this->middlewares as $key => $middleware) {
            $middleware->afterRequest(scrap: $scrap, response: $response);
        }
    }
}
