<?php

declare(strict_types=1);

namespace ScraPHP;

use Closure;
use ScraPHP\HttpClient\HttpClientInterface;
use ScraPHP\HttpClient\HttpClientElementInterface;

class Response
{
    public function __construct(
        private string $url,
        private HttpClientInterface $httpClient
    ){}


    public function bodyHtml(): string
    {
        return $this->httpClient->bodyHtml();
    }

    public function css(string $selector): ?HttpClientElementInterface
    {
        return $this->httpClient->css($selector);
    }

    public function cssEach(string $selector, Closure $closure): void
    {
        $this->httpClient->cssEach($selector, $closure);
    }
}