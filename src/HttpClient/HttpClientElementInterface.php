<?php

declare(strict_types=1);

namespace ScraPHP\HttpClient;


interface HttpClientElementInterface
{
    public function text(): string;
    public function attr(string $attr): string;
    public function each(string $selector, Closure $closure): void;
}