<?php 

declare(strict_types=1);

namespace ScraPHP;

use ScraPHP\Request;

class Request
{

    public const GET = 'GET'; 
    public const POST = 'POST';

    private $failCount = 0;

    public function __construct(
        private string $url,
        private string $method = Request::GET,
        private array $data = [],
    ){}


    public function url(): string
    {
        return $this->url;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function data(): array
    {
        return $this->data;
    }

    public function failCount(): int
    {
        return $this->failCount;
    }
    public function failCountIncrement(): void
    {
        $this->failCount++;
    }
}