<?php

declare(strict_types=1);

namespace ScraPHP\HttpClient\Guzzle;

use ScraPHP\Link;
use ScraPHP\Image;
use ScraPHP\HttpClient\FilteredElement;
use Symfony\Component\DomCrawler\Crawler;
use ScraPHP\Exceptions\InvalidLinkException;
use ScraPHP\Exceptions\InvalidImageException;

final class GuzzleFilteredElement implements FilteredElement
{
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

    public function filterCSS(string $cssSelector): ?FilteredElement
    {
        $crawler = $this->crawler->filter($cssSelector);
        if ($crawler->count() === 0) {
            return null;
        }

        return new GuzzleFilteredElement(crawler: $crawler);
    }

    public function filterCSSEach(string $cssSelector, callable $callback): array
    {
        $filter = $this->crawler->filter($cssSelector);

        return $filter->each(static function (Crawler $crawler, int $i) use ($callback) {
            return $callback(new GuzzleFilteredElement(crawler: $crawler), $i);
        });
    }


    /**
     * Gets a link from a element.
     *
     * @return Link The created link object.
     *
     * @throws InvalidLinkException If unable to get the link.
     */
    public function link(): Link
    {
        $rawUri = $this->crawler->attr('href');
        $baseUri = $this->crawler->getUri();

        if($rawUri === null || $baseUri === null) {
            throw new InvalidLinkException('Unable to get link');
        }

        return new Link(
            text: $this->crawler->text(),
            rawUri: $rawUri,
            baseUri: $baseUri
        );
    }

    /**
     * Gets the image from a element.
     *
     * @return Image The created image.
     *
     * @throws InvalidImageException If unable to get the image.
     */
    public function image(): Image
    {
        $rawUri = $this->crawler->attr('src');
        $baseUri = $this->crawler->getUri();

        if($rawUri === null || $baseUri === null) {
            throw new InvalidImageException('Unable to get image');
        }

        return new Image(
            rawUri: $rawUri,
            baseUri: $baseUri,
            alt: $this->crawler->attr('alt'),
            width: intval($this->crawler->attr('width')),
            height: intval($this->crawler->attr('height')),
        );
    }
}
