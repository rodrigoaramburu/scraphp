<?php

declare(strict_types=1);

namespace ScraPHP\HttpClient\Guzzle;

use ScraPHP\HttpClient\HtmlElement;
use Symfony\Component\DomCrawler\Crawler;

final class GuzzleHtmlElement implements HtmlElement
{
    use FilterCss;
    use FilterCssEach;

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
}
