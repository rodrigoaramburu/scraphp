<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;
use ScraPHP\HttpClient\HttpClient;
use ScraPHP\HttpClient\Page;
use ScraPHP\ProcessPage;
use ScraPHP\ScraPHP;
use ScraPHP\Writers\Writer;

test('bind scraphp methods to instance', function () {

    $pp = new class() extends ProcessPage
    {
        public function process(Page $page): void
        {
        }
    };

    $httpClient = Mockery::mock(HttpClient::class);
    $logger = Mockery::mock(LoggerInterface::class);
    $scraphp = new ScraPHP(
        httpClient: $httpClient,
        logger: $logger,
        writer: Mockery::mock(Writer::class),
    );

    $page = Mockery::mock(Page::class);
    $page
        ->shouldReceive('statusCode')
        ->andReturn(200);

    $httpClient->shouldReceive('get')
        ->with('http://localhost:8000/hello-world.php')
        ->once()
        ->andReturn($page);

    $logger->shouldReceive('info')
        ->once()
        ->with('Accessing http://localhost:8000/hello-world.php');

    $logger->shouldReceive('info')
        ->once()
        ->with('Status: 200 http://localhost:8000/hello-world.php');

    $pp->withScraPHP($scraphp);

    $pp->go('http://localhost:8000/hello-world.php', function (Page $page) {

    });

});
