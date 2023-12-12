<?php 

declare(strict_types=1);

namespace ScraPHP\HttpClient\WebDriver;

use Exception;
use Psr\Log\LoggerInterface;
use ScraPHP\HttpClient\Page;

use ScraPHP\HttpClient\HttpClient;
use Facebook\WebDriver\WebDriverBy;
use ScraPHP\HttpClient\AssetFetcher;
use ScraPHP\Exceptions\HttpClientException;
use Facebook\WebDriver\Chrome\ChromeOptions;
use ScraPHP\Exceptions\UrlNotFoundException;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Exception\NoSuchElementException;

final class WebDriverHttpClient implements HttpClient
{

    private RemoteWebDriver $webDriver;
    private AssetFetcher $assetFetcher;

    /**
     * Constructor for the class.
     *
     * @param LoggerInterface $logger The logger instance.
     */
    public function __construct(
        private LoggerInterface $logger,
        private $webDriverUrl = 'http://localhost:4444'
    )
    {
        $chromeOptions = new ChromeOptions();
        $chromeOptions->addArguments(['-headless']);

        $desiredCapabilities = DesiredCapabilities::chrome();
        $desiredCapabilities->setCapability(ChromeOptions::CAPABILITY, $chromeOptions);

        $this->webDriver = RemoteWebDriver::create($webDriverUrl, $desiredCapabilities);
    
        $this->assetFetcher = new AssetFetcher($this->logger);
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
     * @param string $url The URL of the web page to retrieve.
     * @throws HttpClient An exception that is thrown when an error occurs while accessing the URL.
     * @throws UrlNotFoundException An exception that is thrown when the web page is not found (404 error).
     * @return Page The Page object representing the retrieved web page.
     */
    public function get(string $url): Page
    {
        $this->logger->info('Accessing ' . $url);

        try{
            $this->webDriver->get($url);
        }catch(Exception $e){
            throw new HttpClientException($e->getMessage(), $e->getCode(), $e);
        }

        try{
            $title = $this->webDriver->findElement(WebDriverBy::cssSelector('h1'))->getText();
            if(str_contains( $title , 'Not Found') ){
                $this->logger->error('404 NOT FOUND ' . $url);
                throw new UrlNotFoundException($url);
            }
        }catch(NoSuchElementException $e){
            // ok não é uma página de erro
        }

        $this->logger->info('Status: ' . 200 . ' ' . $url);

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

    public function withLogger(): LoggerInterface
    {
        return $this->logger;
    }

}
