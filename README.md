# ScraPHP

Pequeno web scraper em PHP desenvolvido para uma pequena demanda minha e ainda esta bem cru.

Ele foi pensado para permitir recuperar as informações de uma página utilizando seletores CSS para pegar elementos da página e recuperar seus valores. 

Por padrão ele utiliza o componente *SymfonyHttpClient* para fazer as requisições para as páginas, mas também possui suporte para utilizar o WebDriver Selenium (chrome) para acessar páginas com conteúdo carregado com javascript, embora não forneça as interações como cliques em links, etc.


# Instalação 
```
composer require rodrigoaramburu/scraphp
```
## Uso

Para utilizar o ScraPHP primeiro se deve criar uma classe extendendo a classe *Scrap* e que sobreescreva o método *parse*. Este método recebe um objeto *Response* de parâmetro com a resposta do acesso a página desejada e possui alguns métodos para recupear as informações especificas através de seletores CSS. Utilizamos o *yield* para retornar os valores que selecionamos da página em forma de *array*. Este retorno será passado para um(ou mais) componente *Writer*, este componente tem a função de gravar de alguma forma os dados.

Tendo a classe *Scrap* criamos um objeto dela e chamamos o método *addRequest* que recebe um objeto *Request* que pode ser criado chamando o método *Request::create* passando a url desejada. Podemos adicionar várias requests. Por padrão o request será realizado via GET mas isto pode ser alterado chamando o método *post* do objeto de request e se necessário chamar o método *body* passando um *array* associativo como os inputs de um formulário.

Também adicionamos o *Writer* desejado através do método *addWriter*. O Writer `JsonWriter` como o próprio nome já sugere, grava os dados retornados do método *parse* no formato *json* em um arquivo passado para ele no construtor. Pode-se criar seu próprio writer implementando a interface *WriterInterface*.

Pode-se adicionar *middlwares* que permiter executar ações no inicio e fim de todo o processo de scrap ou antes e depois de cada requisição a uma página. É aconselhável adicionar pelo menos o middleware de log(*LogMiddleware*). É possível criar seu proprio middleware estendendo a classe abstrata *Middleware*.

Por fim, cria-se um objeto da classe *Engine* e adiciona um ou mais scrap através do seu método *scrap* e chama-se o método *start* para iniciar o processamento.

### Um exemplo de uso
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

### Recuperando os dados

O objeto *response* nos dá acesso ao conteúdo da página acessada, nele podemos chamar diversos métodos para recuperar informações desejada da página:

`bodyHtml()`: recupera todo o conteúdo HTML da página, provavelmente não vamos utilizar ele.

`css(string $seletor)`: que retorna um objeto *HttpClientElementInterface* dado um seletor CSS. O objeto *HttpClientElementInterface* possui métodos para recuperar os dados do elemento selecionado.

`cssEach(string $selector, Closure $closure)`: chama a closure passada para cada elemento encontrado com o seletor CSS passando um objeto *HttpClientElementInterface* para cada chamada. Retorna como array o retorno de todas as chamadas da closure.

No objeto `HttpClientElementInterface` temos os métodos:

`text()`: retorna o valor textual do elemento.

`attr(string $attr)`: retorna o conteúdo do atributo passado por parâmetro.

`html()`: retorna o html de dentro do elemento.

`css(string $selector)`: executa um novo seletor css dentro do elemento e retorna um `HttpClientElementInterface`.

`each(string $selector, Closure $closure):`:  executa um novo seletor css dentro do elemento e para cada elemento encontrado executa a closure passada com o `HttpClientElementInterface` para cada elemento.

## Para utilizar webdriver selenium chrome

Para utilziar o WebDriver Selenium (chrome) deve-se chamar o método *useWebDriver* do *Engine*. O método *useWebDriver* aceita como parâmetro um inteiro informando quanto tempo esperar depois da página ser carregada. É útil para esperar algum javascript ser processado, por exemplo o acesso a alguma api, etc.

Executar WebDriver Selenium (chrome) no docker
```
docker run --rm --net=host -p 4444:4444 -p 7900:7900 --shm-size="2g" selenium/standalone-chrome:4.1.2-20220217
```

## Rodar testes

Execute docker compose dentro da pasta tests

```
cd tests
docker-compose up -d
cd ..
composer test
```