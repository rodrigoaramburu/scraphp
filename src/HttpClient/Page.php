<?php 
declare(strict_types=1);

namespace ScraPHP\HttpClient;

use ScraPHP\HttpClient\FilteredElement;

interface Page
{
    public function statusCode(): int;
    public function url(): string;
    public function htmlBody(): string;
    public function headers(): array;
    public function header(string $key): array;

    public function filterCSS(string $cssSelector): ?FilteredElement;
    public function filterCSSEach(string $cssSelector, callable $callback): array;

}
