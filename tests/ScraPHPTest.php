<?php

declare(strict_types=1);

use ScraPHP\ScraPHP;
use ScraPHP\ProcessPage;
use ScraPHP\Writers\Writer;
use Psr\Log\LoggerInterface;
use ScraPHP\HttpClient\Page;
use ScraPHP\Midleware\Middleware;
use Scraphp\HttpClient\HttpClient;
use ScraPHP\HttpClient\Guzzle\GuzzlePage;

beforeEach(function () {
    $this->httpClient = Mockery::mock(HttpClient::class);
    $this->logger = Mockery::mock(LoggerInterface::class);
    $this->writer = Mockery::mock(Writer::class);

    $this->scraphp = new ScraPHP(
        httpClient: $this->httpClient,
        logger: $this->logger,
        writer: $this->writer,
    );
});

afterEach(function () {

    $files = [
        __DIR__.'/assets/texto.txt',
        __DIR__.'/assets/my-filename.txt',
        __DIR__.'/assets/log.txt',
    ];
    foreach ($files as $file) {
        if (file_exists($file)) {
            unlink($file);
        }
    }
});

test('go to a page and return the body', function () {

    $this->httpClient->shouldReceive('get')
        ->once()
        ->with('https://localhost:8000/teste.html')
        ->andReturn(new GuzzlePage(
            content: '<h1>Hello World</h1>',
            statusCode: 200,
            headers: [],
            url: 'https://localhost:8000/teste.html'
        ));

    $this->scraphp->go('https://localhost:8000/teste.html', function (Page $page) {
        expect($page)->toBeInstanceOf(Page::class)
            ->htmlBody()->toBe('<h1>Hello World</h1>')
            ->statusCode()->toBe(200)
            ->headers()->toBe([])
            ->url()->toBe('https://localhost:8000/teste.html');

    });

});

test('bind the context if the callback is a closure', function () {

    $this->httpClient->shouldReceive('get')
        ->once()
        ->with('https://localhost:8000/teste.html')
        ->andReturn(new GuzzlePage(
            content: '<h1>Hello World</h1>',
            statusCode: 200,
            headers: [],
            url: 'https://localhost:8000/teste.html'
        ));

    $this->scraphp->go('https://localhost:8000/teste.html', function (Page $page) {
        expect($this)->toBeInstanceOf(ScraPHP::class);
    });

});

test('call fetch an asset from httpClient', function () {

    $this->httpClient
        ->shouldReceive('fetchAsset')
        ->once()
        ->with('https://localhost:8000/texto.txt')
        ->andReturn('Hello World');

    $content = $this->scraphp->fetchAsset('https://localhost:8000/texto.txt');

    expect($content)->toBe('Hello World');

});

test('call save asset with default filename', function () {

    $this->httpClient
        ->shouldReceive('fetchAsset')
        ->once()
        ->with('https://localhost:8000/texto.txt')
        ->andReturn('Hello World');

    $file = $this->scraphp->saveAsset('https://localhost:8000/texto.txt', __DIR__.'/assets/');

    expect($file)->toBeFile();
    expect(file_get_contents($file))->toBe('Hello World');
});

test('call save asset to save in a relative path  ', function () {
    $this->httpClient
        ->shouldReceive('fetchAsset')
        ->once()
        ->with('https://localhost:8000/texto.txt')
        ->andReturn('Hello World');

    chdir(__DIR__);
    $file = $this->scraphp->saveAsset('https://localhost:8000/texto.txt', 'assets');

    expect($file)->toBeFile();
    expect(file_get_contents($file))->toBe('Hello World');
});

test('throw exception if path is not a directory', function () {

    $this->scraphp->saveAsset('https://localhost:8000/texto.txt', 'not-found-dir');

})->throws(Exception::class, 'not-found-dir is not a directory');

test('call save asset with custom filename', function () {

    $this->httpClient
        ->shouldReceive('fetchAsset')
        ->once()
        ->with('https://localhost:8000/texto.txt')
        ->andReturn('Hello World');

    $file = $this->scraphp->saveAsset(
        'https://localhost:8000/texto.txt',
        __DIR__.'/assets/',
        'my-filename.txt'
    );

    expect($file)->toBeFile();
    expect(file_get_contents($file))->toBe('Hello World');
});



test('call class ProcessPage', function () {

    $this->httpClient->shouldReceive('get')
        ->andReturn(new GuzzlePage(
            content: '<h1>Hello World</h1>',
            statusCode: 200,
            headers: [],
            url: 'https://localhost:8000/teste.html'
        ));

    $pp = Mockery::mock(ProcessPage::class);
    $pp->shouldReceive('withScraPHP')->once()->with($this->scraphp);
    $pp->shouldReceive('process')->once();

    $this->scraphp->go('https://localhost:8000/teste.html', $pp);

});

test('execute middleware with go', function () {

    $page = Mockery::mock(Page::class);
    $page
        ->shouldReceive('statusCode')
        ->once()
        ->andReturn(200);

    $this->httpClient->shouldReceive('get')
        ->once()
        ->with('http://www.example.com')
        ->andReturn($page);


    $this->scraphp->addMidleware(new class () extends Middleware {
        public function processGo(string $url, $handler): Page
        {
            $page = $handler($url);
            expect($page->statusCode())->toBe(200);
            return $page;
        }
    });

    $this->scraphp->go('http://www.example.com', function ($pageR) use ($page) {
        expect($pageR)->toBe($page);
    });

});



test('execute two middlewares with go in sequence', function () {

    $page = Mockery::mock(Page::class);
    $page
        ->shouldReceive('htmlBody')
        ->andReturn('page-content');

    $this->httpClient->shouldReceive('get')
        ->once()
        ->with('http://www.example.com')
        ->andReturn($page);


    $this->scraphp->addMidleware(new class () extends Middleware {
        public function processGo(string $url, $handler): Page
        {
            $page = $handler($url);
            return new GuzzlePage(
                content: $page ->htmlBody().'-middleware1',
                statusCode: 200,
                headers: [],
                url: 'http://www.example.com'
            );
        }
    });
    $this->scraphp->addMidleware(new class () extends Middleware {
        public function processGo(string $url, $handler): Page
        {
            $page = $handler($url);
            return new GuzzlePage(
                content: $page ->htmlBody().'-middleware2',
                statusCode: 200,
                headers: [],
                url: 'http://www.example.com'
            );
        }
    });

    $this->scraphp->go('http://www.example.com', function ($page) {
        expect($page->htmlBody())->toBe('page-content-middleware2-middleware1');
    });

});


test('access httpClient and logger objects inside the middleware', function () {

    $page = Mockery::mock(Page::class);

    $this->httpClient->shouldReceive('get')
        ->once()
        ->with('http://www.example.com')
        ->andReturn($page);


    $this->scraphp->addMidleware(new class () extends Middleware {
        public function processGo(string $url, $handler): Page
        {
            expect($this->httpClient())->toBeInstanceOf(HttpClient::class);
            expect($this->logger())->toBeInstanceOf(LoggerInterface::class);
            return $handler($url);
        }
    });

    $this->scraphp->go('http://www.example.com', function ($pageR) use ($page) {
        expect($pageR)->toBe($page);
    });
});


test('execute middleware with fetchAsset', function () {

    $this->httpClient->shouldReceive('fetchAsset')
        ->once()
        ->with('http://www.example.com/teste.jpg')
        ->andReturn('ASDF');

    $this->scraphp->addMidleware(new class () extends Middleware {
        public function processFetchAsset(string $url, closure $handler): string
        {
            $handler($url);
            return "FDSA";
        }
    });

    $asset = $this->scraphp->fetchAsset('http://www.example.com/teste.jpg');

    expect($asset)->toBe('FDSA');

});



test('execute middleware with saveAsset', function () {

    $this->httpClient->shouldReceive('fetchAsset')
        ->once()
        ->with('https://localhost:8000/texto.txt')
        ->andReturn('ASDF');

    $this->scraphp->addMidleware(new class () extends Middleware {
        public function processSaveAsset(string $url, string $path, ?string $filename = null, closure $handler): string
        {
            $file = $handler($url, $path, $filename);
            return $file.'-middleware';
        }
    });

    $asset = $this->scraphp->saveAsset(
        'https://localhost:8000/texto.txt',
        __DIR__.'/assets/',
        'my-filename.txt'
    );

    expect($asset)->toBe(__DIR__.'/assets//my-filename.txt-middleware');

});


test('execute middleware with go without override processGo', function () {

    $page = Mockery::mock(Page::class);

    $this->httpClient->shouldReceive('get')
        ->once()
        ->with('http://www.example.com')
        ->andReturn($page);


    $this->scraphp->addMidleware(new class () extends Middleware {
    });

    $this->scraphp->go('http://www.example.com', function ($pageR) use ($page) {
        expect($pageR)->toBe($page);
    });
});
