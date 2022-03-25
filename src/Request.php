<?php

declare(strict_types=1);

namespace ScraPHP;

final class Request
{
    public const GET = 'GET';
    public const POST = 'POST';

    private $failCount = 0;

    /**
     * @var array<string,string>
     */
    private array $body = [];

    private function __construct(
        private string $url,
        private string $method = Request::GET
    ) {
    }

    public static function create(string $url): self
    {
        return new Request(url: $url, method: 'GET');
    }

    public function get(): self
    {
        $this->method = 'GET';
        return $this;
    }

    public function post(): self
    {
        $this->method = 'POST';
        return $this;
    }

    public function body(array $body): self
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @return array<string,string>
     */
    public function getBody(): array
    {
        return $this->body;
    }

    public function url(): string
    {
        return $this->url;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function failCount(): int
    {
        return $this->failCount;
    }
    public function failCountIncrement(): void
    {
        $this->failCount++;
    }
}
