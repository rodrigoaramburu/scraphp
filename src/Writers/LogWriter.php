<?php

declare(strict_types=1);

namespace ScraPHP\Writers;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

final class LogWriter implements WriterInterface
{
    private Logger $logger;

    public function __construct(string $stream = 'php://stdout')
    {
        $this->logger = new Logger('ScraPHP');
        $handler = new StreamHandler($stream, Logger::DEBUG);
        $this->logger->pushHandler($handler);
    }

    public function data(array $data): void
    {
        $this->logger->info(\json_encode($data));
    }
}
