<?php

declare(strict_types=1);

namespace ScraPHP\HttpClient;

use Psr\Log\LoggerInterface;
use ScraPHP\Page;

interface HttpClient
{
    public function get(string $url): Page;

    public function fetchAsset(string $url): string;

    public function withLogger(LoggerInterface $logger): self;

    public function logger(): LoggerInterface;
}
