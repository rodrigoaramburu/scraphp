<?php

declare(strict_types=1);

namespace ScraPHP\Writers;

use Psr\Log\LoggerInterface;

abstract class Writer
{
    private LoggerInterface $logger;

    abstract public function write(array $data): void;

    abstract public function exists(array $search): bool;

    /**
     * Sets the logger for the class.
     *
     * @param  LoggerInterface  $logger The logger to be set.
     */
    public function withLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * Gets the logger instance.
     *
     * @return LoggerInterface The logger instance.
     */
    public function logger(): LoggerInterface
    {
        return $this->logger;
    }
}
