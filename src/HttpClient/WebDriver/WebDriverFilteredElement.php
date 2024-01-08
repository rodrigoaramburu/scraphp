<?php

declare(strict_types=1);

namespace ScraPHP\HttpClient\WebDriver;

use ScraPHP\Link;
use ScraPHP\Image;
use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverBy;
use ScraPHP\HttpClient\FilteredElement;
use ScraPHP\Exceptions\InvalidLinkException;
use ScraPHP\Exceptions\InvalidImageException;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\Exception\NoSuchElementException;

final class WebDriverFilteredElement implements FilteredElement
{
    public function __construct(
        private RemoteWebElement $remoteWebElement,
        private WebDriver $webDriver
    ) {
    }

    /**
     * Gets the text content of the element.
     *
     * @return string The text content of the element.
     */
    public function text(): string
    {
        return $this->remoteWebElement->getText();
    }

    /**
     * Gets the value of the specified attribute of element.
     *
     * @param  string  $attr The name of the attribute to get.
     * @return string|null The value of the specified attribute, or null if it does not exist.
     */
    public function attr(string $attr): ?string
    {
        return $this->remoteWebElement->getAttribute($attr);
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
            $remoteWebElement = $this->remoteWebElement->findElement(
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
     * Filters the elements using the given CSS selector and applies a callback function to each element.
     *
     * @param string $cssSelector The CSS selector used to filter the elements.
     * @param callable $callback The callback function to be applied to each filtered element.

     * @return array<int,mixed> An array containing the results of applying the callback function to each filtered element.
     */
    public function filterCSSEach(string $cssSelector, callable $callback): array
    {
        $elements = $this->remoteWebElement->findElements(WebDriverBy::cssSelector($cssSelector));

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
     * Gets a Link object based on the element.
     *
     * @return Link The Link object.
     */
    public function link(): Link
    {
        $rawUri = $this->remoteWebElement->getAttribute('href');
        $baseUri = $this->webDriver->getCurrentURL();

        if($rawUri === null) {
            throw new InvalidLinkException('Unable to get link');
        }
        return new Link(
            text: $this->remoteWebElement->getText(),
            rawUri: $rawUri,
            baseUri: $baseUri
        );
    }


    /**
     * Gets an Image object based on the element.
     *
     * @return Image An Image object.
     * @throws InvalidImageException if unable to retrieve the image.
     */
    public function image(): Image
    {
        $rawUri = $this->remoteWebElement->getAttribute('src');
        $baseUri = $this->webDriver->getCurrentURL();


        if($rawUri === null) {
            throw new InvalidImageException('Unable to get image');
        }

        return new Image(
            rawUri: $rawUri,
            baseUri: $baseUri,
            alt: $this->remoteWebElement->getAttribute('alt'),
            width: intval($this->remoteWebElement->getAttribute('width')),
            height: intval($this->remoteWebElement->getAttribute('height')),
        );
    }
}
