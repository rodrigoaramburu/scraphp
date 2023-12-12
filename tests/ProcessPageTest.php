<?php

declare(strict_types=1);

use ScraPHP\HttpClient\Page;
use ScraPHP\ScraPHP;
use ScraPHP\ProcessPage;
use ScraPHP\HttpClient\HttpClient;

test('bind scraphp methods to instance', function () {

    $pp = new class () extends ProcessPage {
        public function process(Page $page): void
        {
        }
    };

    $scraphp = new ScraPHP();
    $httpClient = Mockery::mock(HttpClient::class);
    
    $httpClient->shouldReceive('withLogger')->once();
    $httpClient->shouldReceive('get')
        ->with('http://localhost:8000/hello-world.php')
        ->once()
        ->andReturn(Mockery::mock(Page::class));
    $scraphp->withHttpClient($httpClient);

    $pp->withScraPHP($scraphp);

    $pp->go('http://localhost:8000/hello-world.php', function (Page $page) {

    });



});
