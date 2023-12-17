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
    public function __construct(
        private RemoteWebDriver $webDriver,
        private int $statusCode,
        private array $headers
    ) {
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    public function url(): string
    {
        return $this->webDriver->getCurrentURL();
    }

    public function htmlBody(): string
    {
        return $this->webDriver->getPageSource();
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function header(string $header): array
    {
        return $this->headers[$header] ?? [];
    }

    public function webDriver(): RemoteWebDriver
    {
        return $this->webDriver;
    }

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
            remoteWebElement: $remoteWebElement
        );
    }

    public function filterCSSEach(string $cssSelector, callable $callback): array
    {
        $elements = $this->webDriver->findElements(WebDriverBy::cssSelector($cssSelector));

        $data = [];
        foreach ($elements as $key => $element) {
            $data[] = $callback(new WebDriverFilteredElement(remoteWebElement: $element), $key);
        }

        return $data;
    }
}
