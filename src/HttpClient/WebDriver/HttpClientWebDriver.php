<?php

declare(strict_types=1);

namespace ScraPHP\HttpClient\WebDriver;

use Closure;
use Exception;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use ScraPHP\HttpClient\HttpClientElementInterface;
use ScraPHP\HttpClient\HttpClientException;
use ScraPHP\HttpClient\HttpClientInterface;
use ScraPHP\Request;
use ScraPHP\Response;
use Symfony\Component\Process\Process;

final class HttpClientWebDriver implements HttpClientInterface
{
    private Process $process;
    private RemoteWebDriver $driver;

    public function __construct(string $url = 'http://localhost:4444')
    {
        $chromeOptions = new ChromeOptions();
        $chromeOptions->addArguments(['-headless']);

        $desiredCapabilities = DesiredCapabilities::chrome();
        $desiredCapabilities->setCapability(ChromeOptions::CAPABILITY, $chromeOptions);

        $this->driver = RemoteWebDriver::create($url, $desiredCapabilities);
    }

    public function __destruct()
    {
        $this->driver->quit();
    }

    public function access(Request $request): Response
    {
        try {
            if ($request->method() === 'GET') {
                return $this->get($request);
            }
            if ($request->method() === 'POST') {
                return $this->post($request);
            }
        } catch (Exception $e) {
            throw new HttpClientException('Erro ao acessar a página: ' . $e->getMessage());
        }
    }

    public function bodyHtml(): string
    {
        return $this->driver->getPageSource();
    }

    public function css(string $selector): ?HttpClientElementInterface
    {
        try {
            $remoteWebElement = $this->driver->findElement(WebDriverBy::cssSelector($selector));
            return new HttpClientWebDriverElement(remoteWebElement: $remoteWebElement, driver: $this->driver);
        } catch (NoSuchElementException $e) {
            return null;
        }
    }

    public function cssEach(string $selector, Closure $closure): void
    {
        $elements = $this->driver->findElements(WebDriverBy::cssSelector($selector));

        foreach ($elements as $key => $element) {
            $closure(new HttpClientWebDriverElement(remoteWebElement: $element, driver: $this->driver), $key);
        }
    }

    public function jsInputFields(array $data): string
    {
        $inputs = '';
        foreach ($data as $key => $value) {
            $inputs .= <<<"JS"
            const hiddenField_{$key} = document.createElement('input');
            hiddenField_{$key}.type = 'hidden';
            hiddenField_{$key}.name = '{$key}';
            hiddenField_{$key}.value = '{$value}';

            form.appendChild(hiddenField_{$key});
        JS;
        }
        return $inputs;
    }

    private function get(Request $request): Response
    {
        $this->driver->get($request->url());

        return new Response(
            url: $request->url(),
            httpClient: $this,
            statusCode: -1
        );
    }

    private function post(Request $request): Response
    {
        $this->driver->get('data:,');

        $inputs = $this->jsInputFields($request->data());

        $script = <<<"JS"
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{$request->url()}';
            document.body.appendChild(form);
            {$inputs}
            form.submit();
            JS;
        $this->driver->executeScript($script);

        return new Response(
            url: $request->url(),
            httpClient: $this,
            statusCode: -1
        );
    }
}
