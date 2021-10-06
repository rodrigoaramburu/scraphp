<?php 

declare(strict_types=1);

namespace ScraPHP;

class Request
{

    public const GET = 'GET'; 
    public const POST = 'POST';

    public function __construct(
        private string $url,
        private string $method = REquest::GET,
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
}