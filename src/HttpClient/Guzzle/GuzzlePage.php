<?php

declare(strict_types=1);

namespace ScraPHP\HttpClient\Guzzle;

use ScraPHP\HttpClient\Page;
use ScraPHP\HttpClient\FilteredElement;
use Symfony\Component\DomCrawler\Crawler;

final class GuzzlePage implements Page
{
    /**
     * Constructs a new instance of the class.
     *
     * @param string $url The URL of the page.
     * @param int $statusCode The status code.
     * @param string $content The content.
     * @param array<string,array<string>> $headers The headers.
     */
    public function __construct(
        private string $url,
        private int $statusCode,
        private string $content,
        private array $headers
    ) {
    }

    /**
     * Get the status code.
     *
     * @return int The status code.
     */
    public function statusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get the URL of the page.
     *
     * @return string The URL of the object.
     */
    public function url(): string
    {
        return $this->url;
    }

    /**
     * Get the HTML body of the page.
     *
     * @return string The HTML body.
     */
    public function htmlBody(): string
    {
        return $this->content;
    }

    /**
     * Get the headers of the page.
     *
     * @return array<string, array<string>> The headers.
     */
    public function headers(): array
    {
        return $this->headers;
    }

    /**
     * Gets the array of headers from a key.
     *
     * @param string $key The header name.
     * @return array<string> The array of header values.
     */
    public function header(string $key): array
    {
        return $this->headers[$key] ?? [];
    }

    /**
    * Filters an element based on the given CSS selector.
    *
    * @param string $cssSelector The CSS selector to filter the elements.
    * @return FilteredElement|null The filtered element or null if no element is found.
    */
    public function filterCSS(string $cssSelector): ?FilteredElement
    {
        $crawler = new Crawler(
            $this->content,
            $this->url
        );
        $crawler = $crawler->filter($cssSelector);
        if ($crawler->count() === 0) {
            return null;
        }

        return new GuzzleFilteredElement(crawler: $crawler);
    }

    /**
     * Filters the elements in the DOM using the given CSS selector and applies a callback function to each element.
     *
     * @param string $cssSelector The CSS selector used to filter the elements.
     * @param callable $callback The callback function to be applied to each filtered element.

     * @return array<int,mixed> An array containing the results of applying the callback function to each filtered element.
     */
    public function filterCSSEach(string $cssSelector, callable $callback): array
    {
        $crawler = new Crawler(
            $this->content,
            $this->url
        );

        $filter = $crawler->filter($cssSelector);

        return $filter->each(static function (Crawler $crawler, int $i) use ($callback) {
            return $callback(new GuzzleFilteredElement(crawler: $crawler), $i);
        });
    }


    /**
     * Gets the title from the page.
     *
     * @return string The title extracted from the HTML content.
     */
    public function title(): string
    {
        $crawler = new Crawler($this->content);

        return $crawler->filter('title')->text();
    }
}
