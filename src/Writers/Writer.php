<?php

declare(strict_types=1);

namespace ScraPHP\Writers;

interface Writer
{
    public function write(array $data): void;

    public function exists(array $search): bool;
}
