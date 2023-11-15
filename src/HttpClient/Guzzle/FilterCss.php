<?php

declare(strict_types=1);

namespace ScraPHP\HttpClient\Guzzle;

use ScraPHP\HttpClient\HtmlElement;
use Symfony\Component\DomCrawler\Crawler;

trait FilterCSS
{
    /**
     * Filters the HTML content using a CSS selector and returns a single matching element.
     *
     * @param string $cssSelector The CSS selector used to filter the HTML content.
     *
     * @return \HtmlElement|null The filtered HTML element or null if no matching element is found.
     */
    public function filterCSS(string $cssSelector): ?HtmlElement
    {
        if (! isset($this->crawler)) {
            $crawler = new Crawler($this->content);
        } else {
            $crawler = $this->crawler;
        }

        $crawler = $crawler->filter($cssSelector);
        if ($crawler->count() === 0) {
            return null;
        }

        return new GuzzleHtmlElement(crawler: $crawler);
    }
}
