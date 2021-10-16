<?php

declare(strict_types=1);

namespace ScraPHP\HttpClient\Simple;

use Closure;
use ScraPHP\HttpClient\HttpClientElementInterface;
use Symfony\Component\DomCrawler\Crawler;

final class HttpClientElement implements HttpClientElementInterface
{
    public function __construct(private Crawler $crawler)
    {
    }

    public function text(): string
    {
        return $this->crawler->text();
    }

    public function attr(string $attr): string
    {
        return $this->crawler->attr($attr);
    }

    public function each(string $selector, Closure $closure): void
    {
        $filter = $this->crawler->filter($selector);
        $data = $filter->each(static function (Crawler $crawler, int $i) use ($closure) {
            return $closure(new HttpClientElement(crawler: $crawler), $i);
        });
    }

    public function html(): string
    {
        return $this->crawler->html();
    }

    public function css(string $selector): ?HttpClientElement
    {
        $crawler = $this->crawler->filter($selector);
        if ($crawler->count() === 0) {
            return null;
        }
        return new HttpClientElement(crawler: $crawler);
    }
}
