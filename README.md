
![example workflow](https://github.com/rodrigoaramburu/scraphp/actions/workflows/main.yml/badge.svg)


# ScraPHP

O *ScraPHP* é uma ferramenta desenvolvida em *PHP* com o objetivo de facilitar a extração de dados (*web scraping*) de páginas da web. Utilizando seletores *CSS*, o *ScraPHP* simplifica o processo de raspagem e permite que os dados sejam salvos em formatos como *JSON*, *CSV* ou diretamente em um Banco de Dados.

Por padrão, o *ScraPHP* utiliza o componente *Guzzle* para realizar requisições às páginas. No entanto, oferece suporte para a utilização do *WebDriver Selenium* (*chrome*) em estágio inicial, permitindo o acesso a páginas que contenham conteúdo carregado através de *Javascript*.


# Instalação 
```
composer require rodrigoaramburu/scraphp
```

## Explicação rápida

Para um uso simplificado utilizamos o método `go` para acessar um endereço web, fornecendo a URL e uma função *closure* para processar o retorno da página. Dentro da *closure*, temos acesso ao objeto `Page`, que oferece métodos como `filterCSS` e `filterCSSEach` para extrair informações da página usando seletores *CSS*. Uma vez obtidas as informações desejadas, podemos armazená-las utilizando o objeto `Writer`. 

Podemos configurar o `Writer` durante a criação do objeto `ScraPHP` por meio dos métodos `withJsonWriter` para salvar em formato *JSON*, `withCSVWriter` para *CSV*, e `withDatabaseWriter` para gravar em um banco de dados (onde é necessário fornecer um objeto `PDO` com a conexão a ser utitlizada e o nome da tabela).


```
$scraphp = ScraPHP::build()
    ->withJsonWriter('posts-botecodigital.json')
    ->create();

$scraphp->go('https://www.botecodigital.dev.br/', function(Page $page){

    $page->filterCSSEach('.post-chamada', function(FilteredElement $element){
        $link = $element->filterCSS('h1 a')->link()->uri();

        if( !$this->writer()->exists(['link' => $link]) )
        {
            $this->logger()->info('Writing: ' . $link);

            $chamada = $element->filterCSS('h1 a')->text();
            $link = $element->filterCSS('h1 a')->link()->uri();
            $autor = $element->filterCSS('.author')->text();
            
            $imageSource = $element->filterCSS('img')->image()->source();

            $imagemPath = $this->saveAsset($imageSource, 'imgs/');

            $this->writer()->write( compact('chamada','link','autor','imageSource','imagemPath'));
        }
    });
});
```

O método `filterCSS` aceita um seletor *CSS* e retorna um objeto `FilteredElement`. Este objeto possui diversos métodos que possibilitam a recuperação de informações específicas do elemento. Por exemplo, utilizando `text()`, podemos obter o texto do elemento, enquanto `attr('nome-atributo')` nos retorna o valor de um atributo. Se o elemento for um *link*, é possível adquirir sua URL com o método `link()->uri()`, ou, no caso de uma imagem, obter o endereço dela utilizando `image()->source()`.

O método `filterCSSEach` é semelhante ao `filterCSS`, com a diferença de que ele realiza uma iteração sobre todos os elementos que correspondem ao seletor *CSS* fornecido. Cada um desses elementos é então enviado como um objeto `FilteredElement` para a função que é passada como parâmetro para que cada um deles possa ser processado. O método `filterCSSEach` retorna um *array* com todos os valores retornados pela função.

Também é fácil realizar o download de arquivo/imagem, o objeto `Page` possui um método `saveAsset()` que recebe o endereço que se deseja baixar e o diretório de destino do arquivo.

Efetuar o *download* de arquivos ou imagens é igualmente simples no *ScraPHP*. O objeto `Page` oferece um método chamado `saveAsset()`, o qual recebe o endereço do arquivo desejado e o diretório de destino para armazenamento do arquivo.

# Executar Selenium

Para realizar as requisições utilizando o *WebDriver Selenium Chrome*  primeiro devemos o web driver rodando, no caso podemos fazer isso facilmente como o docker.

```
docker run --rm --net=host --shm-size="2g" selenium/standalone-chrome:latest
```

Então quando criamos o *ScraPHP* utilizamos o método `withWebDriver()` para ele utilizar o Client Web Driver em vez do Guzzle que é o padrão.

```
$scrap = ScraPHP::build()
    ->withWebDriver()
    ->create();
```
