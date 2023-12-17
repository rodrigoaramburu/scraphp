<?php

declare(strict_types=1);

namespace ScraPHP\HttpClient;

interface HttpClient
{
    public function get(string $url): Page;

    public function fetchAsset(string $url): string;
}
