<?php

declare(strict_types=1);

namespace ScraPHP\HttpClient;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use ScraPHP\Exceptions\AssetNotFoundException;
use ScraPHP\Exceptions\HttpClientException;

final class AssetFetcher
{
    private \GuzzleHttp\Client $client;

    public function __construct()
    {
        $this->client = new \GuzzleHttp\Client();
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
            $response = $this->client->request('GET', $url);
            return $response->getBody()->getContents();
        } catch (ClientException $e) {
            if ($e->getCode() === 404) {
                throw new AssetNotFoundException($url.' not found');
            }
        } catch (ConnectException $e) {
            throw new HttpClientException($e->getMessage(), $e->getCode(), $e);
        }
        return '';
    }
}
