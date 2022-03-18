<?php

declare(strict_types=1);

namespace ScraPHP;

use Closure;
use ScraPHP\HttpClient\HttpClientElementInterface;
use ScraPHP\HttpClient\HttpClientInterface;

final class Response
{
    public function __construct(
        private string $url,
        private HttpClientInterface $httpClient,
        private int $statusCode,
    ) {
    }

    public function bodyHtml(): string
    {
        return $this->httpClient->bodyHtml();
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    public function css(string $selector): ?HttpClientElementInterface
    {
        return $this->httpClient->css($selector);
    }

    public function cssEach(string $selector, Closure $closure): array
    {
        return $this->httpClient->cssEach($selector, $closure);
    }
}
