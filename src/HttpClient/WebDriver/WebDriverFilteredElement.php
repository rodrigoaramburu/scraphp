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

    public function text(): string
    {
        return $this->remoteWebElement->getText();
    }

    public function attr(string $attr): ?string
    {
        return $this->remoteWebElement->getAttribute($attr);
    }

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
     * Gets a Link object based on the properties of the remote web element.
     *
     * @return Link The Link object representing the remote web element.
     */
    public function link(): Link
    {
        $rawUri = $this->remoteWebElement->getAttribute('href');
        $baseUri = $this->webDriver->getCurrentURL();

        if($rawUri === null || $baseUri === null) {
            throw new InvalidLinkException('Unable to get link');
        }
        return new Link(
            text: $this->remoteWebElement->getText(),
            rawUri: $rawUri,
            baseUri: $baseUri
        );
    }


    /**
     * Retrieves an Image object representing the image associated with the remote web element.
     *
     * @return Image An Image object containing information about the image, such as the raw URI,
     *               base URI, alt text, width, and height.
     * @throws InvalidImageException if unable to retrieve the image.
     */
    public function image(): Image
    {
        $rawUri = $this->remoteWebElement->getAttribute('src');
        $baseUri = $this->webDriver->getCurrentURL();


        if($rawUri === null || $baseUri === null) {
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
