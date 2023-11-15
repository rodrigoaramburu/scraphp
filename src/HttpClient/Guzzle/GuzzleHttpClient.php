<?php

declare(strict_types=1);

namespace ScraPHP\HttpClient\Guzzle;

use ScraPHP\Page;
use ScraPHP\HttpClient\HttpClient;
use GuzzleHttp\Exception\ClientException;
use ScraPHP\Exceptions\UrlNotFoundException;
use ScraPHP\Exceptions\AssetNotFoundException;

final class GuzzleHttpClient implements HttpClient
{
    /**
     * Retrieves the contents of a web page using a GET request.
     *
     * @param  string  $url The URL of the web page to retrieve.
     * @throws UrlNotFoundException If the URL could not be found.
     * @return Page The retrieved web page.
     */
    public function get(string $url): Page
    {
        $client = new \GuzzleHttp\Client();
        try {
            $response = $client->request('GET', $url);
        } catch(ClientException $e) {
            throw new UrlNotFoundException($url . ' not found');
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
     * @param string $url The URL of the asset.
     * @throws AssetNotFoundException If the asset could not be found.
     * @return string The contents of the asset.
     */
    public function fetchAsset(string $url): string
    {
        $client = new \GuzzleHttp\Client();
        try {
            $response = $client->request('GET', $url);
        } catch(ClientException $e) {
            throw new AssetNotFoundException($url . ' not found');
        }
        return $response->getBody()->getContents();
    }
}
