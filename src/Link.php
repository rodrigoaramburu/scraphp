<?php

declare(strict_types=1);

namespace ScraPHP;

use Symfony\Component\DomCrawler\UriResolver;

final class Link
{
    /**
     * Constructs a new instance of the class.
     *
     * @param string $text The text of the link.
     * @param string $rawUri The href attribute of the link .
     * @param string|null $baseUri  The url of the page.
     */
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
     * Returns the href attribute.
     *
     * @return string The href attribute.
     */
    public function rawUri(): string
    {
        return $this->rawUri;
    }

    /**
     * Returns the text of the link.
     *
     * @return string The text stored in the object.
     */
    public function text(): string
    {
        return $this->text;
    }

    /**
     * Returns the query parameters from the link.
     *
     * @return array<string,string> The array containing the query parameters.
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
