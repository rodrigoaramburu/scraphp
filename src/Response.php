<?php

declare(strict_types=1);

namespace ScraPHP;

use ScraPHP\HttpClient\HttpClientInterface;

class Response
{
    public function __construct(
        private string $url,
        private HttpClientInterface $httpClient
    ){}


    public function bodyHtml(): string
    {
        return $this->httpClient->bodyHtml();
    }
}