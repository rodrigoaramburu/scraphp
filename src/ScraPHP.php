<?php

declare(strict_types=1);

namespace ScraPHP;

use Closure;
use Exception;
use ScraPHP\Writers\Writer;
use Psr\Log\LoggerInterface;
use ScraPHP\HttpClient\Page;
use ScraPHP\Midleware\Middleware;
use ScraPHP\HttpClient\HttpClient;
use ScraPHP\Exceptions\UrlNotFoundException;
use ScraPHP\Exceptions\AssetNotFoundException;

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

    private ?Middleware $middlewareChain = null;

    /**
     * Constructs a new instance of the class.
     *
     * @param  HttpClient  $httpClient The HTTP client.
     * @param  LoggerInterface  $logger The logger.
     * @param  Writer  $writer The writer.
     */
    public function __construct(
        private HttpClient $httpClient,
        private LoggerInterface $logger,
        private Writer $writer
    ) {
    }

    /**
     * Executes a GET request to the specified URL, pass by middlewares and invokes the provided
     * callback function with the page object.
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
        if($this->middlewareChain !== null) {
            $page = $this->middlewareChain->handleGo($url, function (string $url) {
                return $this->httpClient->get($url);
            });
        } else {
            $page = $this->httpClient->get($url);
        }

        if ($callback instanceof Closure) {
            $callback = \Closure::bind($callback, $this, ScraPHP::class);
            $callback($page);
        }
        if ($callback instanceof ProcessPage) {
            $callback->withScraPHP($this);
            $callback->process($page);
        }

        return $this;
    }


    /**
     * Fetches an asset from the specified URL and pass by middlewares.
     *
     * @param  string  $url The URL of the asset to fetch.
     * @return ?string The contents of the asset.
     *
     * @throws AssetNotFoundException If the asset could not be found.
     */
    public function fetchAsset(string $url): ?string
    {

        if($this->middlewareChain !== null) {
            $data = $this->middlewareChain->handleAssetFetch($url, function (string $url) {
                return  $this->httpClient->fetchAsset($url);
            });
        } else {
            $data = $this->httpClient->fetchAsset($url);
        }

        return $data;
    }

    /**
     * Saves an asset from the given URL to the specified path.
     *
     * @param  string  $url The URL of the asset to be saved.
     * @param  string  $path The path where the asset should be saved.
     * @param  string|null  $filename The name of the file. If not provided, the basename of the URL will be used.
     * @return string The path of the saved asset.
     *
     * @throws AssetNotFoundException If the asset could not be found.
     */
    public function saveAsset(string $url, string $path, string $filename = null): string
    {
        if(!is_dir($path)) {
            throw new Exception($path .' is not a directory');
        }

        if ($filename === null) {
            $filename = basename($url);
        }

        if($this->middlewareChain !== null) {
            return  $this->middlewareChain->handleSaveAsset($url, $path, $filename, function (string $url, string $path, string $filename = null) {
                $content = $this->httpClient->fetchAsset($url);

                file_put_contents($path . '/' . $filename, $content);
                return $path. '/' .$filename;
            });
        } else {
            $content = $this->httpClient->fetchAsset($url);

            file_put_contents($path . '/' . $filename, $content);
            return $path. '/' .$filename;
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

    /**
     * Adds a middleware to the middleware chain.
     *
     * @param Middleware $middleware The middleware to add.
     * @return ScraPHP The modified ScraPHP instance.
     */
    public function addMidleware(Middleware $middleware): ScraPHP
    {
        $middleware->withScraPHP($this);
        if ($this->middlewareChain === null) {
            $this->middlewareChain = $middleware;
        } else {
            $this->middlewareChain->setNext($middleware);
        }

        return $this;

    }

    public static function build(): ScraPHPBuilder
    {
        return new ScraPHPBuilder();
    }
}
