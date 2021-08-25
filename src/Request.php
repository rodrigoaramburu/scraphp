<?php 

declare(strict_types=1);

namespace ScraPHP;

class Request
{
    public function __construct(
        private string $url
    ){}


    public function url(): string
    {
        return $this->url;
    }
}