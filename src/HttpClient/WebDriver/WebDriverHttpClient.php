<?php

declare(strict_types=1);

namespace ScraPHP\HttpClient\WebDriver;

use Exception;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Psr\Log\LoggerInterface;
use ScraPHP\Exceptions\HttpClientException;
use ScraPHP\Exceptions\UrlNotFoundException;
use ScraPHP\HttpClient\AssetFetcher;
use ScraPHP\HttpClient\HttpClient;
use ScraPHP\HttpClient\Page;

final class WebDriverHttpClient implements HttpClient
{
    private RemoteWebDriver $webDriver;

    private AssetFetcher $assetFetcher;

    /**
     * Constructor for the class.
     *
     * @param  LoggerInterface  $logger The logger instance.
     */
    public function __construct(
        private $webDriverUrl = 'http://localhost:4444'
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
     * This method is automatically called when the object is no longer referenced
     * and is about to be destroyed. It is responsible for closing any open resources
     * or connections and performing any necessary cleanup operations.
     */
    public function __destruct()
    {
        $this->webDriver->quit();
    }

    /**
     * Retrieves a web page using the specified URL and returns a Page object.
     *
     * @param  string  $url The URL of the web page to retrieve.
     * @return Page The Page object representing the retrieved web page.
     *
     * @throws HttpClient An exception that is thrown when an error occurs while accessing the URL.
     * @throws UrlNotFoundException An exception that is thrown when the web page is not found (404 error).
     */
    public function get(string $url): Page
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
