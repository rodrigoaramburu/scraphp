<?php

declare(strict_types=1);

namespace ScraPHP;

use Closure;
use ScraPHP\HttpClient\Guzzle\GuzzleHttpClient;
use Scraphp\HttpClient\HttpClient;

final class ScraPHP
{
    private HttpClient $httpClient;

    public function __construct()
    {
        $this->httpClient = new GuzzleHttpClient();
    }

    /**
     * Executes a GET request to the specified URL and invokes the provided callback function with the page object.
     *
     * @param  string  $url The URL to send the GET request to.
     * @param  callable  $callback The callback function to invoke with the response body.
     *
     * @return self Returns an instance of the current class.
     */
    public function go(string $url, Closure $callback): self
    {
        $page = $this->httpClient->get($url);
        if ($callback instanceof Closure) {
            $callback = \Closure::bind($callback, $this, ScraPHP::class);
        }
        $callback($page);

        return $this;
    }

    /**
     * Sets the HTTP client for the object and returns the modified object.
     *
     * @param  HttpClientInterface  $httpClient The HTTP client to be set.
     *
     * @return self The modified object.
     */
    public function withHttpClient(HttpClient $httpClient): self
    {
        $this->httpClient = $httpClient;

        return $this;
    }

    /**
     * Fetches an asset from the specified URL.
     *
     * @param string $url The URL of the asset to fetch.
     * @throws AssetNotFoundException If the asset could not be found.
     * @return string The fetched asset.
     */
    public function fetchAsset(string $url): string
    {
        return $this->httpClient->fetchAsset($url);
    }

    /**
     * Saves an asset from the given URL to the specified path.
     *
     * @param string $url The URL of the asset to be saved.
     * @param string $path The path where the asset should be saved.
     * @param string|null $filename The name of the file. If not provided, the basename of the URL will be used.
     * @throws AssetNotFoundException If the asset could not be found.
     * @return string The path of the saved asset.
 */
    public function saveAsset(string $url, string $path, ?string $filename = null): string
    {
        $content = $this->httpClient->fetchAsset($url);

        if($filename === null) {
            $filename = basename($url);
        }
        file_put_contents($path . $filename, $content);

        return $path . $filename;
    }

    /**
     * Returns the HTTP client instance.
     *
     * @return HttpClient The HTTP client instance.
     */
    public function httpClient(): HttpClient
    {
        return $this->httpClient;
    }
}
