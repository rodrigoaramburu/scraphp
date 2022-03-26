<?php

declare(strict_types=1);

namespace ScraPHP;

use Closure;
use ScraPHP\HttpClient\HttpClientElementInterface;
use ScraPHP\HttpClient\HttpClientInterface;

interface ResponseInterface
{
    public function bodyHtml(): string;

    public function statusCode(): int;

    public function css(string $selector): ?HttpClientElementInterface;

    public function cssEach(string $selector, Closure $closure): array;
}