<?php 
declare(strict_types=1);

namespace ScraPHP\HttpClient;

interface FilteredElement
{
    public function text(): string;

    public function attr(string $attr): ?string;

    public function filterCSS(string $cssSelector): ?FilteredElement;

    public function filterCSSEach(string $cssSelector, callable $callback): array;
    
}
