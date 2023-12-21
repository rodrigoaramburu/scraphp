<?php


declare(strict_types=1);

namespace ScraPHP;

use Symfony\Component\DomCrawler\UriResolver;

final class Image
{
    /**
     * Constructs a new instance of the class.
     *
     * @param string $rawUri The src attribute.
     * @param string $baseUri The url of the page.
     * @param string|null $alt The alternative text.
     * @param int|null $width The width.
     * @param int|null $height The height.
     */
    public function __construct(
        public readonly string $rawUri,
        public readonly string $baseUri,
        public readonly ?string $alt = null,
        public readonly ?int $width = null,
        public readonly ?int $height =  null,
    ) {
    }

    /**
     * Returns the uri of the image.
     *
     * @return string The resolved URI.
     */
    public function source(): string
    {
        return UriResolver::resolve($this->rawUri, $this->baseUri);
    }

    /**
     * Returns the src attribute.
     *
     * @return string The raw URI.
     */
    public function rawUri(): string
    {
        return $this->rawUri;
    }

    /**
     * Returns the value of the alt attribute.
     *
     * @return string|null The value of the alt attribute.
     */
    public function alt(): ?string
    {
        return $this->alt;
    }

    /**
     * Returns the width attribute.
     *
     * @return int|null The width attribute.
     */
    public function width(): ?int
    {
        return $this->width;
    }

    /**
     * Returns the height attribute.
     *
     * @return int|null The height attribute.
     */
    public function height(): ?int
    {
        return $this->height;
    }


}
