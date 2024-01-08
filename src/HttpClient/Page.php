<?php

declare(strict_types=1);

namespace ScraPHP\HttpClient;

interface Page
{
    /**
     * Get the status code.
     *
     * @return int The status code.
     */
    public function statusCode(): int;

    /**
     * Get the URL of the page.
     *
     * @return string The URL of the object.
     */
    public function url(): string;

    /**
     * Get the HTML body of the page.
     *
     * @return string The HTML body.
     */
    public function htmlBody(): string;

    /**
     * Get the headers of the page.
     *
     * @return array<string, array<string>> The headers.
     */
    public function headers(): array;

    /**
     * Gets the array of headers from a key.
     *
     * @param string $key The header name.
     * @return array<string> The array of header values.
     */
    public function header(string $key): array;

    /**
    * Filters an element based on the given CSS selector.
    *
    * @param string $cssSelector The CSS selector to filter the elements.
    * @return FilteredElement|null The filtered element or null if no element is found.
    */
    public function filterCSS(string $cssSelector): ?FilteredElement;

    /**
     * Filters the elements in the DOM using the given CSS selector and applies a callback function to each element.
     *
     * @param string $cssSelector The CSS selector used to filter the elements.
     * @param callable $callback The callback function to be applied to each filtered element.

     * @return array<int,mixed> An array containing the results of applying the callback function to each filtered element.
     */
    public function filterCSSEach(string $cssSelector, callable $callback): array;

    /**
     * Gets the title from the page.
     *
     * @return string The title extracted from the HTML content.
     */
    public function title(): string;
}
