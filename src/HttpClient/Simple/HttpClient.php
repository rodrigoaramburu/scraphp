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
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient as SymfonyHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface as SymfonyHttpClientInterface;

final class HttpClient implements HttpClientInterface
{
    private SymfonyHttpClientInterface $client;
    private string $bodyHtml;
    private int $statusCode;

    public function __construct()
    {
        $this->client = SymfonyHttpClient::create();
    }

    public function access(Request $request): Response
    {
        $this->bodyHtml = '';

        try {
            if ($request->method() === 'GET') {
                return $this->get($request);
            }
            if ($request->method() === 'POST') {
                return  $this->post($request);
            }
            return null;
        } catch (Exception $e) {
            throw new HttpClientException('Erro ao acessar a página: ' . $e->getMessage());
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

    public function cssEach(string $selector, Closure $closure): void
    {
        $crawler = new Crawler($this->bodyHtml);
        $filter = $crawler->filter($selector);
        $data = $filter->each(static function (Crawler $crawler, int $i) use ($closure) {
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
            'body' => $request->getBody(),
        ]);
        $this->bodyHtml = $result->getContent();
        return new Response(
            url: $request->url(),
            httpClient: $this,
            statusCode: $result->getStatusCode(),
        );
    }
}
