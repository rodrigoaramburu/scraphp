<?php

declare(strict_types=1);

include '../../vendor/autoload.php';

use ScraPHP\Engine;
use ScraPHP\HttpClient\HttpClientElementInterface;
use ScraPHP\Middleware\LogMiddleware;
use ScraPHP\Request;
use ScraPHP\Response;
use ScraPHP\Scrap;
use ScraPHP\Writers\JsonWriter;

final class QuoteScrap extends Scrap
{
    public function parse(Response $response): Generator
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
$engine->useWebDriver(webDriverUrl: 'http://localhost:4444');

$scrap = new QuoteScrap();

$scrap->addWriter(new JsonWriter('quotes.json'))
    //->middleware( new DelayMiddleware(secs: 30))
    ->middleware(new LogMiddleware())
    ->addRequest(Request::create(url: 'http://quotes.toscrape.com/page/1/'));

$engine->scrap($scrap)
    ->start();
