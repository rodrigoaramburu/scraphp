<?php 

declare(strict_types=1);

namespace ScraPHP\HttpClient;

use Closure;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Remote\RemoteWebElement;
use ScraPHP\HttpClient\HttpClientElementInterface;

class HttpClientWebDriverElement implements HttpClientElementInterface
{
    public function __construct(private RemoteWebElement $remoteWebElement){}

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
            $closure( new HttpClientWebDriverElement( remoteWebElement: $element), $key);
        }
    }
}