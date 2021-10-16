<?php

declare(strict_types=1);

namespace ScraPHP\HttpClient;

use Closure;

interface HttpClientElementInterface
{
    public function text(): string;
    public function attr(string $attr): string;
    public function each(string $selector, Closure $closure): void;
    public function html(): string;
    public function css(string $selector): ?HttpClientElementInterface;
}
