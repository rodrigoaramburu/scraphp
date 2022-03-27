<?php

declare(strict_types=1);

namespace ScraPHP;

final class Request
{
    private $failCount = 0;

    /**
     * @var array<string,string>
     */
    private array $body = [];
    private string $method = 'GET';

    private function __construct(
        private string $url,
    ) {
    }

    public static function create(string $url): self
    {
        return new Request(url: $url);
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

    public function isGet(): bool
    {
        return $this->method === 'GET';
    }

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    public function withBody(array $body): self
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @return array<string,string>
     */
    public function body(): array
    {
        return $this->body;
    }

    public function url(): string
    {
        return $this->url;
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
