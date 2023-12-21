<?php


declare(strict_types=1);

namespace ScraPHP;

use Symfony\Component\DomCrawler\UriResolver;

final class Image
{
    public function __construct(
        public readonly string $rawUri,
        public readonly string $baseUri,
        public readonly ?string $alt = null,
        public readonly ?int $width = null,
        public readonly ?int $height =  null,
    ) {
    }

    public function source(): string
    {
        return UriResolver::resolve($this->rawUri, $this->baseUri);
    }

    public function rawUri(): string
    {
        return $this->rawUri;
    }

    public function alt(): ?string
    {
        return $this->alt;
    }

    public function width(): ?int
    {
        return $this->width;
    }

    public function height(): ?int
    {
        return $this->height;
    }


}
