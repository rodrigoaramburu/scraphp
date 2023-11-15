<?php

declare(strict_types=1);

namespace ScraPHP\HttpClient\Guzzle;

use ScraPHP\HttpClient\HttpClient;
use ScraPHP\Page;

final class GuzzleHttpClient implements HttpClient
{
    /**
     * Retrieves the contents of a web page using a GET request.
     *
     * @param  string  $url The URL of the web page to retrieve.
     *
     * @return Page The retrieved web page.
     */
    public function get(string $url): Page
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', $url);

        return new Page(
            url: $url,
            statusCode: $response->getStatusCode(),
            content: $response->getBody()->getContents(),
            headers: $response->getHeaders(),
            httpClient: $this
        );
    }
}
