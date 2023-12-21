<?php

declare(strict_types=1);

namespace ScraPHP\HttpClient\WebDriver;

use Exception;
use ScraPHP\HttpClient\Page;
use ScraPHP\HttpClient\HttpClient;
use Facebook\WebDriver\WebDriverBy;
use ScraPHP\HttpClient\AssetFetcher;
use ScraPHP\Exceptions\HttpClientException;
use Facebook\WebDriver\Chrome\ChromeOptions;
use ScraPHP\Exceptions\UrlNotFoundException;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use ScraPHP\Exceptions\AssetNotFoundException;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Exception\NoSuchElementException;

final class WebDriverHttpClient implements HttpClient
{
    private RemoteWebDriver $webDriver;

    private AssetFetcher $assetFetcher;

    /**
     * Constructs a new instance of the class.
     *
     * @param string $webDriverUrl The URL of the WebDriver (default: 'http://localhost:4444')
     *
     */
    public function __construct(
        string $webDriverUrl = 'http://localhost:4444'
    ) {
        $chromeOptions = new ChromeOptions();
        $chromeOptions->addArguments(['-headless']);

        $desiredCapabilities = DesiredCapabilities::chrome();
        $desiredCapabilities->setCapability(ChromeOptions::CAPABILITY, $chromeOptions);

        $this->webDriver = RemoteWebDriver::create($webDriverUrl, $desiredCapabilities);

        $this->assetFetcher = new AssetFetcher();
    }

    /**
     * Destructor method for the class.
     *
     * Closes the WebDriver.
     */
    public function __destruct()
    {
        $this->webDriver->quit();
    }

    /**
     * Retrieves a web page using the specified URL and returns a Page object.
     *
     * @param  string  $url The URL of the web page to retrieve.
     * @return ?Page The Page object representing the retrieved web page.
     *
     * @throws UrlNotFoundException An exception that is thrown when the web page is not found (404 error).
     * @throws HttpClientException If an error occurs during the HTTP request.
     */
    public function get(string $url): ?Page
    {

        try {
            $this->webDriver->get($url);
        } catch (Exception $e) {
            throw new HttpClientException($e->getMessage(), $e->getCode(), $e);
        }

        try {
            $title = $this->webDriver->findElement(WebDriverBy::cssSelector('h1'))->getText();
            if (str_contains($title, 'Not Found')) {
                throw new UrlNotFoundException($url);
            }
        } catch (NoSuchElementException $e) {
            // ok não é uma página de erro
        }

        return new WebDriverPage(
            webDriver: $this->webDriver,
            statusCode: 200,
            headers: []
        );
    }

    /**
     * Fetches an asset from the given URL.
     *
     * @param  string  $url The URL of the asset.
     * @return string The contents of the asset.
     *
     * @throws AssetNotFoundException If the asset could not be found.
     */
    public function fetchAsset(string $url): string
    {
        return $this->assetFetcher->fetchAsset($url);
    }
}
