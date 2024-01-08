<?php

declare(strict_types=1);

namespace ScraPHP\Writers;

interface Writer
{
    /**
     * Writes data.
     *
     * @param  array<string, mixed>  $data The data to be written.
     */
    public function write(array $data): void;

    /**
     * Checks if the given search criteria exists.
     *
     * @param  array<string, mixed>  $search The search criteria to check.
     * @return bool Returns true if the search criteria exists, false otherwise.
     */
    public function exists(array $search): bool;
}
