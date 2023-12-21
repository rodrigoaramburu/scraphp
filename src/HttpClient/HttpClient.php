<?php

declare(strict_types=1);

namespace ScraPHP\HttpClient;

use ScraPHP\Exceptions\HttpClientException;
use ScraPHP\Exceptions\UrlNotFoundException;
use ScraPHP\Exceptions\AssetNotFoundException;

interface HttpClient
{
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
    public function get(string $url): ?Page;


    /**
     * Fetches an asset from the given URL.
     *
     * @param  string  $url The URL of the asset.
     *
     * @return string The contents of the asset.
     *
     * @throws AssetNotFoundException If the asset could not be found.
     */
    public function fetchAsset(string $url): string;
}
