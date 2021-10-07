<?php 

declare(strict_types=1);

namespace ScraPHP\Writers;

interface WriterInterface{
    public function data(array $data): void;
}