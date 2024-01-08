<?php

declare(strict_types=1);

namespace ScraPHP\Midleware;

use ScraPHP\HttpClient\Page;

final class LogMiddleware extends Middleware
{
    public function processGo(string $url, \closure $handler): Page
    {
        $this->logger()->info('Accessing: ' . $url);
        $page = $handler($url);
        $this->logger()->info('Accessed: '. $page->statusCode() . ' ' . $url);

        return $page;
    }

    public function processAssetFetch(string $url, \closure $handler): string
    {
        $this->logger()->info('Fetching: ' . $url);
        $asset = $handler($url);
        $this->logger()->info('Fetched: ' . $url);

        return $asset;
    }

    public function processSaveAsset(String $url, string $path, string $filename, \closure $handler): string
    {
        $this->logger()->info('Saving asset: ' . $url);
        $asset = $handler($url, $path, $filename);
        $this->logger()->info('Asset Saved: ' . $asset);

        return $asset;
    }
}
