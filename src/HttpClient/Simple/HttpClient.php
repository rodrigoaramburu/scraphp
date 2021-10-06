<?php 

declare(strict_types=1);

namespace ScraPHP\HttpClient\Simple;

use Closure;

use Exception;
use ScraPHP\Request;
use ScraPHP\Response;
use Symfony\Component\DomCrawler\Crawler;
use ScraPHP\HttpClient\HttpClientException;

use ScraPHP\HttpClient\HttpClientInterface;
use ScraPHP\HttpClient\Simple\HttpClientElement;
use ScraPHP\HttpClient\HttpClientElementInterface;
use Symfony\Component\HttpClient\HttpClient as SymfonyHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface as SymfonyHttpClientInterface;

class HttpClient implements HttpClientInterface
{
    private SymfonyHttpClientInterface $client;
    private string $bodyHtml;

    public function __construct()
    {
        $this->client = SymfonyHttpClient::create();
    }

    public function access(Request $request): Response
    {
        $this->bodyHtml = '';

        try{
            if($request->method() == 'GET'){
                $this->get($request);
            }
            if($request->method() == 'POST'){
                $this->post($request);
            }
        }catch(Exception $e){
            throw new HttpClientException('Erro ao acessar a página: ' . $e->getMessage());
        }
        
        return new Response(
            url: $request->url(),
            httpClient: $this
        );
    }
    
    private function get(Request $request): void 
    {
        $result = $this->client->request('GET', $request->url());
        $this->bodyHtml = $result->getContent(); 
    }

    private function post(Request $request): void 
    {
        $result = $this->client->request('POST', $request->url(),[
            'body' => $request->data()
        ]);
        $this->bodyHtml = $result->getContent(); 
    }

    public function bodyHtml(): string
    {
        return $this->bodyHtml;
    }

    public function css(string $selector): HttpClientElementInterface
    {
        $crawler = new Crawler($this->bodyHtml);
        return new HttpClientElement( crawler: $crawler->filter($selector) );
    }

    public function cssEach(string $selector, Closure $closure): void
    {
        $crawler = new Crawler($this->bodyHtml);
        $filter = $crawler->filter($selector);
        $data = $filter->each( function(Crawler $crawler, int $i) use ($closure){
            return $closure( new HttpClientElement( crawler: $crawler) , $i);
        });
    }

}