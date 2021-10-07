<?php
declare(strict_types=1);
include('../vendor/autoload.php');

use ScraPHP\Scrap;
use ScraPHP\Engine;
use ScraPHP\Request;
use ScraPHP\Response;
use ScraPHP\Writers\LogWriter;
use ScraPHP\Writers\JsonWriter;
use ScraPHP\HttpClient\HttpClientElementInterface;


class QuoteScrap extends Scrap
{
    public function parse(Response $response): Generator
    {
        $data = [];
        $response->cssEach('.quote', function(HttpClientElementInterface $element) use(&$data){
            
            $data[] = [
                'text' => $element->css('.text')->text(),
                'author' => $element->css('.author')->text()
            ];
        });
        
        $url =  $response->css('.pager .next a')?->attr('href');
        if($url !== null) $this->addRequest( new Request(url: 'http://quotes.toscrape.com' . $url) );

        yield $data;
    }
}

$engine = new Engine();

$scrap = new QuoteScrap();
$scrap->addWriter( new LogWriter());
$scrap->addWriter( new JsonWriter('quotes.json'));

$scrap->addRequest(new Request(url: 'http://quotes.toscrape.com/page/1/'));

$engine->scrap($scrap);
$engine->start();