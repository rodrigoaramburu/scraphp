<?php 

declare(strict_types=1);

namespace ScraPHP\HttpClient;

use ScraPHP\Request;
use ScraPHP\Response;

interface HttpClientInterface
{
    public function access(Request $request): Response;
}