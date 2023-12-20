<?php

declare(strict_types=1);

namespace ScraPHP\HttpClient\Guzzle;

use ScraPHP\HttpClient\Page;
use ScraPHP\HttpClient\FilteredElement;
use Symfony\Component\DomCrawler\Crawler;

final class GuzzlePage implements Page
{
    public function __construct(
        private string $url,
        private int $statusCode,
        private string $content,
        private array $headers
    ) {
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    public function url(): string
    {
        return $this->url;
    }

    public function htmlBody(): string
    {
        return $this->content;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function header(string $key): array
    {
        return $this->headers[$key] ?? [];
    }

    public function filterCSS(string $cssSelector): ?FilteredElement
    {
        $crawler = new Crawler(
            $this->content,
            $this->url
        );
        $crawler = $crawler->filter($cssSelector);
        if ($crawler->count() === 0) {
            return null;
        }

        return new GuzzleFilteredElement(crawler: $crawler);
    }

    public function filterCSSEach(string $cssSelector, callable $callback): array
    {
        $crawler = new Crawler(
            $this->content,
            $this->url
        );

        $filter = $crawler->filter($cssSelector);

        return $filter->each(static function (Crawler $crawler, int $i) use ($callback) {
            return $callback(new GuzzleFilteredElement(crawler: $crawler), $i);
        });
    }


    public function title(): string
    {
        $crawler = new Crawler($this->content);

        return $crawler->filter('title')->text();
    }
}
