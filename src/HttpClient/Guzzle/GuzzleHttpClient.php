<?php

declare(strict_types=1);

namespace ScraPHP\HttpClient\Guzzle;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use ScraPHP\Exceptions\AssetNotFoundException;
use ScraPHP\Exceptions\HttpClientException;
use ScraPHP\Exceptions\UrlNotFoundException;
use ScraPHP\HttpClient\AssetFetcher;
use ScraPHP\HttpClient\HttpClient;
use ScraPHP\HttpClient\Page;

final class GuzzleHttpClient implements HttpClient
{
    private \GuzzleHttp\Client $client;

    private AssetFetcher $assetFetcher;

    /**
     * Constructor for the class.
     */
    public function __construct()
    {
        $this->client = new \GuzzleHttp\Client();
        $this->assetFetcher = new AssetFetcher();
    }

    /**
     * Retrieves the contents of a web page using a GET request.
     *
     * @param  string  $url The URL of the web page to retrieve.
     *
     * @return Page The retrieved web page.
     *
     * @throws UrlNotFoundException If the URL could not be found.
     * @throws HttpClientException If an error occurs during the HTTP request.
     */
    public function get(string $url): ?Page
    {
        try {
            $response = $this->client->request('GET', $url);
            return new GuzzlePage(
                url: $url,
                statusCode: $response->getStatusCode(),
                content: $response->getBody()->getContents(),
                headers: $response->getHeaders()
            );
        } catch (ClientException $e) {
            if ($e->getCode() === 404) {
                throw new UrlNotFoundException($url.' not found');
            }
        } catch (ConnectException $e) {
            throw new HttpClientException($e->getMessage(), $e->getCode(), $e);
        }

        return null;
    }

    /**
     * Fetches an asset from the given URL.
     *
     * @param  string  $url The URL of the asset.
     *
     * @return string The contents of the asset.
     *
     * @throws AssetNotFoundException If the asset could not be found.
     */
    public function fetchAsset(string $url): string
    {
        return $this->assetFetcher->fetchAsset($url);
    }
}
