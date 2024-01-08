<?php

declare(strict_types=1);

namespace ScraPHP\HttpClient\WebDriver;

use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use ScraPHP\HttpClient\FilteredElement;
use ScraPHP\HttpClient\Page;

final class WebDriverPage implements Page
{
    /**
     * Constructs a new instance of the class.
     *
     * @param RemoteWebDriver $webDriver The remote web driver.
     * @param int $statusCode The status code.
     * @param array<string, array<string>> $headers The headers.
     */
    public function __construct(
        private RemoteWebDriver $webDriver,
        private int $statusCode,
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
        return $this->webDriver->getCurrentURL();
    }

    /**
     * Get the HTML body of the page.
     *
     * @return string The HTML body.
     */
    public function htmlBody(): string
    {
        return $this->webDriver->getPageSource();
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
        try {
            $remoteWebElement = $this->webDriver->findElement(
                WebDriverBy::cssSelector($cssSelector)
            );
        } catch (NoSuchElementException $exception) {
            return null;
        }

        return new WebDriverFilteredElement(
            remoteWebElement: $remoteWebElement,
            webDriver: $this->webDriver
        );
    }

    /**
     * Filters the elements using the given CSS selector and applies a callback function
     * to each element.
     *
     * @param string $cssSelector The CSS selector used to filter the elements.
     * @param callable $callback The callback function to be applied to each filtered element.

     * @return array<int,mixed> An array containing the results of applying the callback function
     *                          to each filtered element.
     */
    public function filterCSSEach(string $cssSelector, callable $callback): array
    {
        $elements = $this->webDriver->findElements(WebDriverBy::cssSelector($cssSelector));

        $data = [];
        foreach ($elements as $key => $element) {
            $data[] = $callback(
                new WebDriverFilteredElement(
                    remoteWebElement: $element,
                    webDriver: $this->webDriver
                ),
                $key
            );
        }

        return $data;
    }

    /**
     * Gets the title from the page.
     *
     * @return string The title extracted from the HTML content.
     */
    public function title(): string
    {
        return $this->webDriver->getTitle();
    }


    /**
     * Get the web driver.
     *
     * @return RemoteWebDriver The web driver.
     */
    public function webDriver(): RemoteWebDriver
    {
        return $this->webDriver;
    }
}
