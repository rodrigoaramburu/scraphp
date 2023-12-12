<?php

declare(strict_types=1);

namespace ScraPHP\HttpClient\Guzzle;

use ScraPHP\HttpClient\FilteredElement;
use ScraPHP\HttpClient\HtmlElement;
use Symfony\Component\DomCrawler\Crawler;

final class GuzzleFilteredElement implements FilteredElement
{

    public function __construct(private Crawler $crawler)
    {
    }

    /**
     * Gets the text content of the element.
     *
     * @return string The text content of the element.
     */
    public function text(): string
    {
        return $this->crawler->text();
    }

    /**
     * Gets the value of the specified attribute of element.
     *
     * @param  string  $attr The name of the attribute to get.
     * @return string|null The value of the specified attribute, or null if it does not exist.
     */
    public function attr(string $attr): ?string
    {
        return $this->crawler->attr($attr);
    }

    public function filterCSS(string $cssSelector): ?FilteredElement{
        $crawler = $this->crawler->filter($cssSelector);
        if ($crawler->count() === 0) {
            return null;
        }

        return new GuzzleFilteredElement(crawler: $crawler);
    }

    public function filterCSSEach(string $cssSelector, callable $callback): array
    {     
        $filter = $this->crawler->filter($cssSelector);

        return $filter->each(static function (Crawler $crawler, int $i) use ($callback) {
            return $callback(new GuzzleFilteredElement(crawler: $crawler), $i);
        });
    }
}
