<?php

declare(strict_types=1);

namespace ScraPHP;

use Closure;
use Exception;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use ScraPHP\Exceptions\AssetNotFoundException;
use ScraPHP\Exceptions\HttpClientException;
use ScraPHP\Exceptions\UrlNotFoundException;
use ScraPHP\HttpClient\HttpClient;
use ScraPHP\HttpClient\Page;
use ScraPHP\Writers\Writer;

final class ScraPHP
{
    /**
     * @var array<string, Closure|ProcessPage> The list of URL errors.
     */
    private array $urlErrors = [];

    /**
     * @var array<string, Closure|ProcessPage> The list of assets errors.
     */
    private array $assetErrors = [];

    /**
     * Constructs a new instance of the class.
     *
     * @param  HttpClient  $httpClient The HTTP client.
     * @param  LoggerInterface  $logger The logger.
     * @param  Writer  $writer The writer.
     * @param  int  $retryCount The number of times to retry.
     * @param  int  $retryTime The time to wait between retries.
     */
    public function __construct(
        private HttpClient $httpClient,
        private LoggerInterface $logger,
        private Writer $writer,
        private int $retryCount = 3,
        private int $retryTime = 30
    ) {
    }

    /**
     * Executes a GET request to the specified URL and invokes the provided callback
     * function with the page object.
     *
     * @param  string  $url The URL to send the GET request to.
     * @param  Closure|ProcessPage  $callback The Closure function or class ProcessPage to invoke with
     *      the Page object representing the retrieved web page.
     * @return self Returns an instance of the current class.
     *
     * @throws UrlNotFoundException If the URL could not be found.
     */
    public function go(string $url, Closure|ProcessPage $callback): self
    {
        try {
            $page = $this->tryGetPage($url);

            if ($callback instanceof Closure) {
                $callback = \Closure::bind($callback, $this, ScraPHP::class);
                $callback($page);
            }
            if ($callback instanceof ProcessPage) {
                $callback->withScraPHP($this);
                $callback->process($page);
            }
        } catch (HttpClientException|UrlNotFoundException $e) {
            $this->urlErrors[] = ['url' => $url, 'pageProcessor' => $callback];
            $this->logger->error('cant get url: '.$url);
        }

        return $this;
    }

    /**
     * Tries to get a page from the given URL.
     *
     * @param  string  $url The URL of the page to retrieve.
     * @return Page The retrieved page.
     *
     * @throws HttpClientException If an error occurs while making the HTTP request.
     * @throws UrlNotFoundException If the URL could not be found.
     */
    private function tryGetPage(string $url): ?Page
    {
        $tries = 0;
        while ($tries < $this->retryCount) {
            try {
                $this->logger->info('Accessing '.$url);
                $page = $this->httpClient->get($url);
                $this->logger->info('Status: '.$page->statusCode().' '.$url);

                return $page;
            } catch (UrlNotFoundException $e) {
                $this->logger->error('404 NOT FOUND '.$url);
            } catch (HttpClientException $e) {
                $this->logger->error('Error: '.$e->getMessage());
            }
            $tries++;
            if ($tries >= $this->retryCount) {
                throw $e;
            }
            $this->logger->info('Retry in ('.($this->retryTime * $tries).') seconds: '.$url);
            sleep($this->retryTime * $tries);
        }

        return null;
    }

    /**
     * Fetches an asset from the specified URL.
     *
     * @param  string  $url The URL of the asset to fetch.
     * @return ?string The contents of the asset.
     *
     * @throws AssetNotFoundException If the asset could not be found.
     */
    public function fetchAsset(string $url): ?string
    {
        try {
            return $this->tryGetAsset($url);
        } catch (HttpClientException $e) {
            $this->assetErrors[] = ['url' => $url];
            $this->logger->error('cant get asset: '.$url);
        }

        return null;
    }

    /**
     * Saves an asset from the given URL to the specified path.
     *
     * @param  string  $url The URL of the asset to be saved.
     * @param  string  $path The path where the asset should be saved.
     * @param  string|null  $filename The name of the file. If not provided, the basename of the URL will be used.
     * @return ?string The path of the saved asset.
     *
     * @throws AssetNotFoundException If the asset could not be found.
     */
    public function saveAsset(string $url, string $path, string $filename = null): ?string
    {
        if(!is_dir($path)){
            throw new Exception($path .' is not a directory');
        }

        try {
            $content = $this->tryGetAsset($url);
            if ($filename === null) {
                $filename = basename($url);
            }

            file_put_contents($path . '/' . $filename, $content);

            return $path. '/' .$filename;

        } catch (HttpClientException $e) {
            $this->assetErrors[] = ['url' => $url];
            $this->logger->error('cant get asset: '.$url);
        }

        return null;
    }

    /**
     * Tries to get an asset from a given URL.
     *
     * @param  string  $url The URL of the asset.
     * @return string The fetched asset.
     *
     * @throws AssetNotFoundException If the asset could not be found.
     * @throws HttpClientException If an error occurs during the HTTP request.
     */
    private function tryGetAsset(string $url): ?string
    {
        $tries = 0;
        while ($tries < $this->retryCount) {
            try {
                $this->logger->info('Fetching asset: '.$url);
                $data = $this->httpClient->fetchAsset($url);
                $this->logger->info('Fetched: '.$url);

                return $data;
            } catch (AssetNotFoundException $e) {
                $this->logger->error('404 NOT FOUND '.$url);
            } catch (HttpClientException $e) {
                $tries++;
                $this->logger->error('Error: '.$e->getMessage());
                if ($tries >= $this->retryCount) {
                    throw $e;
                }
                $this->logger->info('Retry in ('.($this->retryTime * $tries).') seconds: '.$url);
                sleep($this->retryTime * $tries);
            }

        }

        return null;
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

    /**
     * Gets the logger object.
     *
     * @return LoggerInterface The logger object.
     */
    public function logger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * Gets the writer object.
     *
     * @return Writer The writer object.
     */
    public function writer(): Writer
    {
        return $this->writer;
    }

    /**
     * Gets the current retry count.
     *
     * @return int The current retry count.
     */
    public function retryCount(): int
    {
        return $this->retryCount;
    }

    /**
     * Get the retry time.
     *
     * @return int The retry time.
     */
    public function retryTime(): int
    {
        return $this->retryTime;
    }

    /**
     * Gets the list of URL errors.
     *
     * @return array<string, Closure|ProcessPage> The list of URL errors.
     */
    public function urlErrors(): array
    {
        return $this->urlErrors;
    }

    /**
     * Gets the list of asset errors.
     *
     * @return array<string, Closure|ProcessPage> The list of asset errors.
     */
    public function assetErrors(): array
    {
        return $this->assetErrors;
    }

    public static function build(): ScraPHPBuilder
    {
        return new ScraPHPBuilder();
    }
}
