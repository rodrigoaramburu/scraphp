<?php

declare(strict_types=1);

include '../../vendor/autoload.php';

use ScraPHP\Scrap;
use ScraPHP\Engine;
use ScraPHP\Request;
use ScraPHP\Response;
use ScraPHP\ResponseInterface;
use ScraPHP\Writers\JsonWriter;
use ScraPHP\Middleware\LogMiddleware;
use ScraPHP\Middleware\DelayMiddleware;
use ScraPHP\HttpClient\HttpClientElementInterface;

final class QuoteScrap extends Scrap
{
    public function parse(ResponseInterface $response): Generator
    {
        $data = $response->cssEach('.quote', static function (HttpClientElementInterface $element){
            return [
                'text' => $element->css('.text')->text(),
                'author' => $element->css('.author')->text(),
            ];
        });

        $url = $response->css('.pager .next a')?->attr('href');
        if ($url !== null) {
            $this->addRequest(Request::create(url: 'http://quotes.toscrape.com' . $url));
        }
        yield $data;
    }
}

$engine = new Engine();
//$engine->useWebDriver(webDriverUrl: 'http://localhost:4444');

$scrap = new QuoteScrap();

$scrap->withWriter(new JsonWriter('quotes.json'))
    ->withMiddleware( new DelayMiddleware(secs: 30))
    ->withMiddleware(new LogMiddleware())
    ->addRequest(Request::create(url: 'http://quotes.toscrape.com/page/1/'));

$engine->scrap($scrap)
    ->start();
