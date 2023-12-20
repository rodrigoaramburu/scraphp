<?php

declare(strict_types=1);

namespace ScraPHP;

use Symfony\Component\DomCrawler\UriResolver;

final class Link
{
    public function __construct(
        private string $text,
        private string $rawUri,
        private ?string $baseUri = null
    ) {

    }

    /**
     * Returns the URI from the link.
     *
     * @return string The generated URI.
     */
    public function uri(): string
    {
        return UriResolver::resolve($this->rawUri, $this->baseUri);
    }

    /**
     * Returns the raw URI.
     *
     * @return string The raw URI.
     */
    public function rawUri(): string
    {
        return $this->rawUri;
    }

    /**
     * Gets the text of the link.
     *
     * @return string The text stored in the object.
     */
    public function text(): string
    {
        return $this->text;
    }

    /**
     * Get the query parameters from the link.
     *
     * @return array The array containing the query parameters.
     */
    public function query(): array
    {
        $urlParsed = parse_url($this->rawUri);
        if(isset($urlParsed['query'])) {
            parse_str($urlParsed['query'], $query);
            return $query;
        }
        return [];
    }
}
