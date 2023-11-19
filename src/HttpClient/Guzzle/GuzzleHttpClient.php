<?php

declare(strict_types=1);

namespace ScraPHP\HttpClient\Guzzle;

use GuzzleHttp\Exception\ClientException;
use Psr\Log\LoggerInterface;
use ScraPHP\Exceptions\AssetNotFoundException;
use ScraPHP\Exceptions\UrlNotFoundException;
use ScraPHP\HttpClient\HttpClient;
use ScraPHP\Page;

final class GuzzleHttpClient implements HttpClient
{
    public function __construct(private LoggerInterface $logger)
    {

    }

    /**
     * Retrieves the contents of a web page using a GET request.
     *
     * @param  string  $url The URL of the web page to retrieve.
     * @return Page The retrieved web page.
     *
     * @throws UrlNotFoundException If the URL could not be found.
     */
    public function get(string $url): Page
    {
        $client = new \GuzzleHttp\Client();
        try {
            $this->logger->debug('Accessing '.$url);
            $response = $client->request('GET', $url);
            $this->logger->debug('Status: '.$response->getStatusCode().' '.$url);
        } catch (ClientException $e) {
            if ($e->getCode() === 404) {
                $this->logger->error('404 NOT FOUND '.$url);
                throw new UrlNotFoundException($url.' not found');
            }
            throw $e;
        }

        return new Page(
            url: $url,
            statusCode: $response->getStatusCode(),
            content: $response->getBody()->getContents(),
            headers: $response->getHeaders(),
            httpClient: $this
        );
    }

    /**
     * Fetches an asset from the given URL.
     *
     * @param  string  $url The URL of the asset.
     * @return string The contents of the asset.
     *
     * @throws AssetNotFoundException If the asset could not be found.
     */
    public function fetchAsset(string $url): string
    {
        $client = new \GuzzleHttp\Client();
        try {
            $this->logger->debug('Fetching asset '.$url);
            $response = $client->request('GET', $url);
            $this->logger->debug('Status: '.$response->getStatusCode().' '.$url);
        } catch (ClientException $e) {
            if ($e->getCode() === 404) {
                $this->logger->error('404 NOT FOUND '.$url);
                throw new AssetNotFoundException($url.' not found');
            }
            throw $e;
        }

        return $response->getBody()->getContents();
    }

    public function withLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    public function logger(): LoggerInterface
    {
        return $this->logger;
    }
}
