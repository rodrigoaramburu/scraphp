<?php

declare(strict_types=1);

namespace ScraPHP\HttpClient\Guzzle;

use ScraPHP\Page;
use Psr\Log\LoggerInterface;
use ScraPHP\HttpClient\HttpClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use ScraPHP\Exceptions\HttpClientException;
use ScraPHP\Exceptions\UrlNotFoundException;
use ScraPHP\Exceptions\AssetNotFoundException;

final class GuzzleHttpClient implements HttpClient
{
    private \GuzzleHttp\Client $client;
    /**
     * Constructor for the class.
     *
     * @param LoggerInterface $logger The logger instance.
     */
    public function __construct(private LoggerInterface $logger)
    {
        $this->client = new \GuzzleHttp\Client();

    }

    /**
     * Retrieves the contents of a web page using a GET request.
     *
     * @param  string  $url The URL of the web page to retrieve.
     * @return Page The retrieved web page.
     *
     * @throws UrlNotFoundException If the URL could not be found.
     * @throws HttpClientException If an error occurs during the HTTP request.
     */
    public function get(string $url): Page
    {
        try {
            $this->logger->info('Accessing '.$url);
            $response = $this->client->request('GET', $url);
            $this->logger->info('Status: '.$response->getStatusCode().' '.$url);
        } catch (ClientException $e) {
            if ($e->getCode() === 404) {
                $this->logger->error('404 NOT FOUND '.$url);
                throw new UrlNotFoundException($url.' not found');
            }
        } catch(ConnectException $e) {
            throw new HttpClientException($e->getMessage(), $e->getCode(), $e);
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
        try {
            $this->logger->info('Fetching asset '.$url);
            $response = $this->client->request('GET', $url);
            $this->logger->info('Status: '.$response->getStatusCode().' '.$url);
        } catch (ClientException $e) {
            if ($e->getCode() === 404) {
                $this->logger->error('404 NOT FOUND '.$url);
                throw new AssetNotFoundException($url.' not found');
            }
        } catch(ConnectException $e) {
            throw new HttpClientException($e->getMessage(), $e->getCode(), $e);
        }

        return $response->getBody()->getContents();
    }

}
