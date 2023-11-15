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
     * Returns the HTTP client instance.
     *
     * @return HttpClient The HTTP client instance.
     */
    public function httpClient(): HttpClient
    {
        return $this->httpClient;
    }
}
