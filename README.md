# ScraPHP

Pequeno web scraper em PHP. Ainda em desenvolvimento inicial.

* Permite pegar informações utilizando seletores css
* Permiter usar WebDriver Selenium para acessar páginas com conteudo carregado com javascript


# Adicionando no Projeto 
```
composer require rodrigoaramburu/scraphp
```


## Exemplo 
```
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
//$engine->useWebDriver();

$scrap = new QuoteScrap();

$scrap->addWriter(new JsonWriter('quotes.json'))
    //->middleware( new DelayMiddleware(secs: 30))
    ->middleware(new LogMiddleware())
    ->addRequest(Request::create(url: 'http://quotes.toscrape.com/page/1/'));

$engine->scrap($scrap)
    ->start();
```

## Para utilizar webdriver selenium chrome

Deve chamar o método useWebDriver do do Engine.

Executar docker
```
docker run --rm --net=host -p 4444:4444 -p 7900:7900 --shm-size="2g" selenium/standalone-chrome:4.1.2-20220217
```

## Rodar testes

Execute docker compose dentro da pasta tests

cd tests
docker-compose up -d
cd ..
composer test
