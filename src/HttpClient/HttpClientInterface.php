<?php

declare(strict_types=1);

namespace ScraPHP\HttpClient;

use Closure;
use ScraPHP\Request;
use ScraPHP\ResponseInterface;

interface HttpClientInterface
{
    public function access(Request $request): ResponseInterface;

    public function bodyHtml(): string;

    public function css(string $selector): ?HttpClientElementInterface;

    public function cssEach(string $selector, Closure $closure): array;
}
