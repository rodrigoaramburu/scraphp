<?php

declare(strict_types=1);

namespace ScraPHP\HttpClient\Guzzle;

use Symfony\Component\DomCrawler\Crawler;

trait FilterCssEach
{
    /**
     * Filters the elements in the HTML content using a CSS selector and applies a callback function to each element.
     *
     * @param string $cssSelector The CSS selector used to filter the elements.
     * @param callable $callback The callback function to process to each filtered element.
     *
     * @return array<string> An array containing the results of processing the callback function to each filtered element.
     */
    public function filterCSSEach(string $cssSelector, callable $callback): array
    {
        if (! isset($this->crawler)) {
            $crawler = new Crawler($this->content);
        } else {
            $crawler = $this->crawler;
        }

        $filter = $crawler->filter($cssSelector);

        return $filter->each(static function (Crawler $crawler, int $i) use ($callback) {
            return $callback(new GuzzleHtmlElement(crawler: $crawler), $i);
        });
    }
}
