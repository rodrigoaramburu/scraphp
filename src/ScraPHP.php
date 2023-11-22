<?php

declare(strict_types=1);

namespace ScraPHP;

use Closure;
use Monolog\Level;
use Monolog\Logger;
use ScraPHP\Writers\Writer;
use Monolog\Handler\StreamHandler;
use Scraphp\HttpClient\HttpClient;
use Monolog\Formatter\LineFormatter;
use Psr\Log\LoggerInterface;
use ScraPHP\Exceptions\UrlNotFoundException;
use ScraPHP\Exceptions\AssetNotFoundException;
use ScraPHP\Exceptions\HttpClientException;
use ScraPHP\HttpClient\Guzzle\GuzzleHttpClient;

final class ScraPHP
{
    private HttpClient $httpClient;

    private LoggerInterface $logger;

    private Writer $writer;

    private array $urlErrors = [];
    private array $assetErrors = [];

    private array $config;

    /**
     * Constructs a new instance of the class.
     *
     * @param  array<string, array<string,string>>  $config An array of configuration options.
     *    - 'logger': (array) An array of configuration options for the logger.
     *        - 'filename': (string) The filename of the log file. Defaults to 'php://stdout'.
     *    - 'httpclient': (array) An array of configuration options for the HTTP client.
     *        - 'retry_count': (int) The number of times to retry a failed request. Defaults to 3.
     *        - 'retry_time': (int) The number of seconds to wait between retries. Defaults to 30.
     *
     * @throws Exception If an error occurs during initialization.
     */
    public function __construct(array $config = [])
    {

        $config['logger']['filename'] = $config['logger']['filename'] ?? 'php://stdout';

        $config['httpclient']['retry_count'] = $config['httpclient']['retry_count'] ?? 3;
        $config['httpclient']['retry_time'] = $config['httpclient']['retry_time'] ?? 30;

        $this->config = $config;

        $this->initLogger($config['logger']['filename']);

        $this->httpClient = new GuzzleHttpClient($this->logger);
    }

    /**
     * Executes a GET request to the specified URL and invokes the provided callback function with the page object.
     *
     * @param  string  $url The URL to send the GET request to.
     * @param  callable|ProcessPage  $callback The callback function or class ProcessPage to invoke with the response body.
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
        } catch(HttpClientException $e) {
            $this->urlErrors[] = [ 'url' => $url, 'pageProcessor' => $callback];
            $this->logger->error('cant get url: '.$url);
        }


        return $this;
    }


    /**
     * Tries to get a page from the given URL.
     *
     * @param string $url The URL of the page to retrieve.
     * @throws HttpClientException If an error occurs while making the HTTP request.
     * @return Page The retrieved page.
     */
    private function tryGetPage(string $url): Page
    {
        $tries = 0;
        while($tries < $this->config['httpclient']['retry_count']) {
            try {
                return $this->httpClient->get($url);
            } catch(HttpClientException $e) {
                $tries++;
                $this->logger->error('Error: '.$e->getMessage());
                if($tries >= $this->config['httpclient']['retry_count']) {
                    throw $e;
                }
                $this->logger->info('Retry in ('.($this->config['httpclient']['retry_time'] * $tries).') seconds: '.$url);
                sleep($this->config['httpclient']['retry_time'] * $tries);
            }

        }
    }

    /**
     * Sets the HTTP client for the object and returns the modified object.
     *
     * @param  HttpClientInterface  $httpClient The HTTP client to be set.
     * @return self The modified object.
     */
    public function withHttpClient(HttpClient $httpClient): self
    {
        $this->httpClient = $httpClient;
        $httpClient->withLogger($this->logger);

        return $this;
    }

    /**
     * Sets the writer for the object and returns the object itself.
     *
     * @param Writer $writer The writer object to set.
     * @return self The updated object with the new writer.
     */
    public function withWriter(Writer $writer): self
    {
        $this->writer = $writer;
        $this->writer->withLogger($this->logger);

        return $this;
    }


    /**
     * Sets a logger for the current object and returns the object itself.
     *
     * @param LoggerInterface $logger The logger to be set.
     * @return self The modified object.
     */
    public function withLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;
        return $this;
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
        } catch(HttpClientException $e) {
            $this->assetErrors[] = [ 'url' => $url];
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
    public function saveAsset(string $url, string $path, ?string $filename = null): ?string
    {

        try {
            $content = $this->tryGetAsset($url);
            if ($filename === null) {
                $filename = basename($url);
            }
            file_put_contents($path.$filename, $content);

            return $path . $filename;

        } catch(HttpClientException $e) {
            $this->assetErrors[] = [ 'url' => $url];
            $this->logger->error('cant get asset: '.$url);
        }

        return null;
    }


    /**
     * Tries to get an asset from a given URL.
     *
     * @param string $url The URL of the asset.
     * @throws HttpClientException If an error occurs during the HTTP request.
     * @return string The fetched asset.
     */
    private function tryGetAsset(string $url): string
    {
        $tries = 0;
        while($tries < $this->config['httpclient']['retry_count']) {
            try {
                return $this->httpClient->fetchAsset($url);
            } catch(HttpClientException $e) {
                $tries++;
                $this->logger->error('Error: '.$e->getMessage());
                if($tries >= $this->config['httpclient']['retry_count']) {
                    throw $e;
                }
                $this->logger->info('Retry in ('.($this->config['httpclient']['retry_time'] * $tries).') seconds: '.$url);
                sleep($this->config['httpclient']['retry_time'] * $tries);
            }

        }
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
     * @return Logger The logger object.
     */
    public function logger(): Logger
    {
        return $this->logger;
    }

    /**
     * Initializes the logger.
     *
     * @param  string  $logfile The path to the log file.
     *
     * @throws Exception If there is an error initializing the logger.
     */
    private function initLogger(string $logfile): void
    {
        $this->logger = new Logger('SCRAPHP');
        $handler = new StreamHandler($logfile, Level::Debug);
        $formatter = new LineFormatter("%datetime% %level_name%  %message% %context% %extra%\n", 'Y-m-d H:i:s');
        $handler->setFormatter($formatter);
        $this->logger->pushHandler($handler);
    }

    /**
     * Gets the list of URL errors.
     *
     * @return array The list of URL errors.
     */
    public function urlErrors(): array
    {
        return $this->urlErrors;
    }
    /**
     * Gets the list of asset errors.
     *
     * @return array The list of asset errors.
     */
    public function assetErrors(): array
    {
        return $this->assetErrors;
    }
}
