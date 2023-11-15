<?php

declare(strict_types=1);

namespace ScraPHP\HttpClient;

use ScraPHP\Page;

interface HttpClient
{
    public function get(string $url): Page;
}
