<?php

declare(strict_types=1);

namespace ScraPHP\Midleware;

use ScraPHP\ScraPHP;
use Psr\Log\LoggerInterface;
use ScraPHP\HttpClient\Page;
use ScraPHP\HttpClient\HttpClient;

abstract class Middleware
{
    private ?Middleware $next = null;

    private ?ScraPHP $scraphp = null;

    /**
     * Sets the next middleware in the chain.
     *
     * @param Middleware $next The next middleware to be set.
     * @return void
     */
    public function setNext(Middleware $next): void
    {
        $this->next = $next;
    }

    /**
     * Retrieves the next middleware in the chain.
     *
     * @return Middleware The next middleware in the chain.
     */
    public function next(): Middleware
    {
        return $this->next;
    }

    /**
     * Passes the closure processGo to the next middleware in the chain if it exists.
     *
     * @param String $url The URL to be processed.
     * @param \Closure $processGo The closure to process the URL.
     * @return Page The processed page.
     */
    public function handleGo(String $url, \closure $processGo): Page
    {
        $handler = function ($url) use ($processGo) {
            if ($this->next !== null) {
                return $this->next->handleGo($url, $processGo);
            }

            return $processGo($url);
        };

        return $this->processGo($url, $handler);
    }

    /**
     * Process the given URL using the provided handler function.
     * Should be override
     *
     * @param string $url The URL to be processed.
     * @param \closure $handler The handler function to process the URL.
     * @return Page The processed page.
     */
    protected function processGo(string $url, \closure $handler): Page
    {
        return $handler($url);
    }


    /**
     * Passes the closure processFetchAsset to the next middleware in the chain if it exists.
     *
     * @param String $url The URL of the asset to fetch.
     * @param \closure $processFetchAsset The closure to process the fetched asset.
     * @return string The processed asset.
     */
    public function handleAssetFetch(String $url, \closure $processFetchAsset): string
    {
        $handler = function ($url) use ($processFetchAsset) {
            if ($this->next !== null) {
                return $this->next->handleAssetFetch($url, $processFetchAsset);
            }

            return $processFetchAsset($url);
        };

        return $this->processFetchAsset($url, $handler);
    }

    /**
     * Process the fetch asset handler function.
     * Should be override
     *
     * @param string $url The URL of the asset.
     * @param \closure $handler The handler for processing the asset.
     * @return string The processed asset.
     */
    protected function processFetchAsset(string $url, \closure $handler): string
    {
        return $handler($url);
    }

    /**
     * Passes the closure processSaveAsset to the next middleware in the chain if it exists.
     *
     * @param String $url The URL of the asset.
     * @param string $path The path where the asset will be saved.
     * @param string $filename The filename of the asset.
     * @param \closure $processSaveAsset A closure for processing the save operation.
     * @return string The result of the save operation.
     */
    public function handleSaveAsset(string $url, string $path, string $filename, \closure $processSaveAsset): string
    {
        $handler = function ($url, $path, $filename) use ($processSaveAsset) {
            if ($this->next !== null) {
                return $this->next->handleSaveAsset($url, $path, $filename, $processSaveAsset);
            }

            return $processSaveAsset($url, $path, $filename);
        };

        return $this->processSaveAsset($url, $path, $filename, $handler);
    }

    /**
     * Process the  save an asset handler funcion.
     *
     * @param string   $url      The URL of the asset.
     * @param string   $path     The path where the asset will be saved.
     * @param string   $filename The name of the asset file.
     * @param \closure $handler  The closure that handles the asset processing.
     * @return string The result of the asset processing.
     */
    protected function processSaveAsset(string $url, string $path, string $filename, \closure $handler): string
    {
        return $handler($url, $path, $filename);
    }


    /**
     * Sets the ScraPHP object for this instance.
     *
     * @param ScraPHP $scraphp The ScraPHP object to be set.
     * @return self Returns an instance of the current class.
     */
    public function withScraPHP(ScraPHP $scraphp): self
    {
        $this->scraphp = $scraphp;
        return $this;
    }

    /**
     * Returns the HTTP client instance.
     *
     * @return HttpClient The HTTP client instance.
     */
    protected function httpClient(): HttpClient
    {
        return $this->scraphp->httpClient();
    }

    /**
     * Retrieve the logger object.
     *
     * @return LoggerInterface The logger object.
     */
    protected function logger(): LoggerInterface
    {
        return $this->scraphp->logger();
    }




}
