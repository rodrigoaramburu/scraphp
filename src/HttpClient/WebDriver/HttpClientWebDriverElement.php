<?php

declare(strict_types=1);

namespace ScraPHP\HttpClient\WebDriver;

use Closure;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use ScraPHP\HttpClient\HttpClientElementInterface;

final class HttpClientWebDriverElement implements HttpClientElementInterface
{
    public function __construct(private RemoteWebElement $remoteWebElement, private RemoteWebDriver $driver)
    {
    }

    public function text(): string
    {
        return $this->remoteWebElement->getText();
    }

    public function attr(string $attr): string
    {
        return $this->remoteWebElement->getAttribute($attr);
    }

    public function each(string $selector, Closure $closure): array
    {
        $elements = $this->remoteWebElement->findElements(WebDriverBy::cssSelector($selector));

        $data = [];
        foreach ($elements as $key => $element) {
            $data[] = $closure(new HttpClientWebDriverElement(remoteWebElement: $element, driver: $this->driver), $key);
        }
        return $data;
    }

    public function html(): string
    {
        return $this->driver->executeScript('return arguments[0].innerHTML', [$this->remoteWebElement]);
    }

    public function css(string $selector): ?HttpClientElementInterface
    {
        try {
            $remoteWebElement = $this->remoteWebElement->findElement(WebDriverBy::cssSelector($selector));
            return new HttpClientWebDriverElement(
                remoteWebElement: $remoteWebElement,
                driver: $this->driver
            );
        } catch (NoSuchElementException $e) {
            return null;
        }
    }
}
