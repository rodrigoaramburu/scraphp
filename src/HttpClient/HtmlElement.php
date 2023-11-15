<?php

declare(strict_types=1);

namespace ScraPHP\HttpClient;

interface HtmlElement
{
    public function text(): string;

    public function attr(string $attr): ?string;
}
