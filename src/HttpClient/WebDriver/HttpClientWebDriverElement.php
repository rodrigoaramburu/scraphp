<?php 

declare(strict_types=1);

namespace ScraPHP\HttpClient\WebDriver;

use Closure;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use ScraPHP\HttpClient\HttpClientElementInterface;
use Facebook\WebDriver\Exception\NoSuchElementException;

class HttpClientWebDriverElement implements HttpClientElementInterface
{
    public function __construct(private RemoteWebElement $remoteWebElement, private RemoteWebDriver $driver){}

    public function text(): string
    {
        return $this->remoteWebElement->getText();
    }

    public function attr(string $attr): string
    {
        return $this->remoteWebElement->getAttribute($attr);
    }

    public function each(string $selector, Closure $closure): void
    {
        $elements = $this->remoteWebElement->findElements(WebDriverBy::cssSelector($selector));

        foreach($elements as $key => $element){
            $closure( new HttpClientWebDriverElement( remoteWebElement: $element, driver: $this->driver), $key);
        }
    }

    public function html(): string
    {
        $innerHtml = $this->driver->executeScript('return arguments[0].innerHTML', [$this->remoteWebElement]);
        return $innerHtml; 
    } 

    public function css(string $selector): ?HttpClientElementInterface
    {
        try{
            $remoteWebElement = $this->remoteWebElement->findElement( WebDriverBy::cssSelector($selector) );
            return new HttpClientWebDriverElement( 
                remoteWebElement: $remoteWebElement,
                driver: $this->driver
            );
        }catch(NoSuchElementException $e){
            return null;
        }
    }
}