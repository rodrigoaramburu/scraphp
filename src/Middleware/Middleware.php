<?php

declare(strict_types=1);

namespace ScraPHP\Middleware;

use ScraPHP\Request;
use ScraPHP\Response;
use ScraPHP\Scrap;

abstract class Middleware
{
    public function beforeAll(Scrap $scrap): void
    {
    }
    public function afterAll(Scrap $scrap): void
    {
    }
    public function beforeRequest(Scrap $scrap, Request $request): void
    {
    }
    public function afterRequest(Scrap $scrap, Response $response): void
    {
    }
}
