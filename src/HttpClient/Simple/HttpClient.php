<?php

declare(strict_types=1);

namespace ScraPHP\HttpClient\Simple;

use Closure;
use Exception;
use ScraPHP\HttpClient\HttpClientElementInterface;
use ScraPHP\HttpClient\HttpClientException;
use ScraPHP\HttpClient\HttpClientInterface;
use ScraPHP\Request;
use ScraPHP\Response;
use ScraPHP\ResponseInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient as SymfonyHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface as SymfonyHttpClientInterface;

final class HttpClient implements HttpClientInterface
{
    private SymfonyHttpClientInterface $client;
    private string $bodyHtml;

    public function __construct(array $options = [])
    {
        $this->client = SymfonyHttpClient::create($options);
    }

    public function access(Request $request): ResponseInterface
    {
        $this->bodyHtml = '';

        try {
            if ($request->isGet()) {
                return $this->get($request);
            }
            if ($request->isPost()) {
                return $this->post($request);
            }
        } catch (Exception $e) {
            throw new HttpClientException($e->getMessage());
        }
    }

    public function bodyHtml(): string
    {
        return $this->bodyHtml;
    }

    public function css(string $selector): ?HttpClientElementInterface
    {
        $crawler = new Crawler($this->bodyHtml);
        $crawler = $crawler->filter($selector);
        if ($crawler->count() === 0) {
            return null;
        }
        return new HttpClientElement(crawler: $crawler);
    }

    public function cssEach(string $selector, Closure $closure): array
    {
        $crawler = new Crawler($this->bodyHtml);
        $filter = $crawler->filter($selector);
        return $filter->each(static function (Crawler $crawler, int $i) use ($closure) {
            return $closure(new HttpClientElement(crawler: $crawler), $i);
        });
    }

    private function get(Request $request): Response
    {
        $result = $this->client->request('GET', $request->url());
        $this->bodyHtml = $result->getContent();
        return new Response(
            url: $request->url(),
            httpClient: $this,
            statusCode: $result->getStatusCode(),
        );
    }

    private function post(Request $request): Response
    {
        $result = $this->client->request('POST', $request->url(), [
            'body' => $request->body(),
        ]);
        $this->bodyHtml = $result->getContent();
        return new Response(
            url: $request->url(),
            httpClient: $this,
            statusCode: $result->getStatusCode(),
        );
    }
}
