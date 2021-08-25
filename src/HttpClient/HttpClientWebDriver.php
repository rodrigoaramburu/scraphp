<?php

declare(strict_types=1);

namespace ScraPHP\HttpClient;

use Closure;
use ScraPHP\Request;
use ScraPHP\Response;
use Facebook\WebDriver\WebDriverBy;
use Symfony\Component\Process\Process;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use ScraPHP\HttpClient\HttpClientElementInterface;
use ScraPHP\HttpClient\HttpClientWebDriverElement;


class HttpClientWebDriver implements HttpClientInterface
{
    private Process $process;
    private RemoteWebDriver $driver;
    
    public function __construct()
    {
        $chromeOptions = new ChromeOptions();
        $chromeOptions->addArguments(['-headless']);

        $desiredCapabilities = DesiredCapabilities::chrome();
        $desiredCapabilities->setCapability(ChromeOptions::CAPABILITY, $chromeOptions);

        $this->driver = RemoteWebDriver::create('http://localhost:4444', $desiredCapabilities);

    }

    public function __destruct()
    {
        $this->driver->quit();
    }
    
    public function access(Request $request): Response
    {
        $this->driver->get($request->url());


        return new Response(
            url: $request->url(),
            httpClient: $this
        );
    }

    public function bodyHtml(): string
    {
        return $this->driver->getPageSource();
    }

    public function css(string $selector): HttpClientElementInterface
    {
        return new HttpClientWebDriverElement( 
            remoteWebElement: $this->driver->findElement( WebDriverBy::cssSelector($selector) )
        );
    }

    public function cssEach(string $selector, Closure $closure): void
    {
        $elements = $this->driver->findElements( WebDriverBy::cssSelector($selector) );

        foreach ($elements as $key => $element) {
            $closure(new HttpClientWebDriverElement( remoteWebElement: $element), $key);
        }
    }
}