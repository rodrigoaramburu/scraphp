<?php 

declare(strict_types=1);

namespace ScraPHP\HttpClient\WebDriver;

use Facebook\WebDriver\WebDriverBy;
use ScraPHP\HttpClient\FilteredElement;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\Exception\NoSuchElementException;

final class WebDriverFilteredElement implements FilteredElement
{

    public function __construct(
        private RemoteWebElement $remoteWebElement
    ){}

    public function text(): string
    {
        return $this->remoteWebElement->getText();
    }

    public function attr(string $attr): ?string
    {
        return $this->remoteWebElement->getAttribute($attr);
    }

    public function filterCSS(string $cssSelector): ?FilteredElement
    {
        try{
            $remoteWebElement = $this->remoteWebElement->findElement(
                WebDriverBy::cssSelector($cssSelector)
            );
        }catch (NoSuchElementException $exception){
            return null;
        }
        return new WebDriverFilteredElement(
            remoteWebElement: $remoteWebElement
        );
    }

    public function filterCSSEach(string $cssSelector, callable $callback): array
    {
        $elements = $this->remoteWebElement->findElements(WebDriverBy::cssSelector($cssSelector));

        $data = [];
        foreach ($elements as $key => $element) {
            $data[] = $callback(new WebDriverFilteredElement(remoteWebElement: $element), $key);
        }
        return $data;
    }
}