<?php

declare(strict_types=1);

namespace ScraPHP\HttpClient;

use Closure;
use ScraPHP\Request;
use ScraPHP\Response;

interface HttpClientInterface
{
    public function access(Request $request): Response;

    public function bodyHtml(): string;

    public function css(string $selector): ?HttpClientElementInterface;

    public function cssEach(string $selector, Closure $closure): void;
}
