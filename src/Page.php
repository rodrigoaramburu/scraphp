<?php

declare(strict_types=1);

namespace ScraPHP;

use ScraPHP\HttpClient\Guzzle\FilterCss;
use ScraPHP\HttpClient\Guzzle\FilterCssEach;
use ScraPHP\HttpClient\HttpClient;

final class Page
{
    use FilterCss;
    use FilterCssEach;

    /**
     * Initializes a new instance of the class.
     *
     * @param  string  $url The current url of the page.
     * @param  string  $content The body of the page.
     * @param  int  $statusCode The status code of the requisition.
     * @param  array<string, array<string>>  $headers The headers of the requisition.
     */
    public function __construct(
        private string $url,
        private int $statusCode,
        private string $content,
        private array $headers,
        private HttpClient $httpClient
    ) {
    }

    /**
     * Get the URL.
     *
     * @return string The URL.
     */
    public function url(): string
    {
        return $this->url;
    }

    /**
     * Retrieves the status code.
     *
     * @return int The status code.
     */
    public function statusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Retrieves the content of the page.
     *
     * @return string The content of the page.
     */
    public function content(): string
    {
        return $this->content;
    }

    /**
     * Retrieves the headers of the page.
     *
     * @return array<string,string> The headers of the page.
     */
    public function headers(): array
    {
        return $this->headers;
    }

    /**
     * Retrieves the array of values associated with the given header.
     *
     * @param  string  $header the name of the header to retrieve values for
     * @return array<string> the array of values associated with the given header, or an empty array if the header does not exist
     */
    public function header(string $header): array
    {
        return $this->headers[$header] ?? [];
    }
}
