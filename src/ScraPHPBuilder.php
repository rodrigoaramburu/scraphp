<?php

declare(strict_types=1);

namespace ScraPHP;

use Monolog\Level;
use Monolog\Logger;
use ScraPHP\Writers\Writer;
use Psr\Log\LoggerInterface;
use ScraPHP\Writers\CSVWriter;
use ScraPHP\Writers\JsonWriter;
use Monolog\Handler\StreamHandler;
use ScraPHP\HttpClient\HttpClient;
use ScraPHP\Writers\DatabaseWriter;
use Monolog\Formatter\LineFormatter;
use ScraPHP\HttpClient\Guzzle\GuzzleHttpClient;
use ScraPHP\HttpClient\WebDriver\WebDriverHttpClient;
use ScraPHP\Midleware\Middleware;

final class ScraPHPBuilder
{
    private ?HttpClient $httpClient = null;

    private ?LoggerInterface $logger = null;

    private ?Writer $writer = null;

    /**
     * @var array<Middleware>
     */
    private array $middlewares = [];

    /**
     * Sets the HttpClient and returns itself.
     *
     * @param  HttpClient  $httpClient The HttpClient to be set.
     *
     * @return self Returns the current object instance.
     */
    public function withHttpClient(HttpClient $httpClient): self
    {
        $this->httpClient = $httpClient;

        return $this;
    }

    /**
     * Sets the Logger and returns itself. If a string was passed in, it
     * will be create a Logger to this file.
     *
     * @param  LoggerInterface|string  $logger The logger to be set for the object.
     *
     * @return self Returns the current object instance.
     */
    public function withLogger(LoggerInterface|string $logger): self
    {
        if (is_string($logger)) {
            $this->logger = $this->createDefaultLogger($logger);

            return $this;
        }

        $this->logger = $logger;

        return $this;
    }

    /**
     * Sets the writer.
     *
     * @param  Writer  $writer The writer object to be set.
     *
     * @return self Returns the current object instance.
     */
    public function withWriter(Writer $writer): self
    {
        $this->writer = $writer;

        return $this;
    }


    /**
     * Create a web driver client for the ScraPHP class.
     *
     * @param string $url The URL of the WebDriver. Default is 'http://localhost:4444'.
     *
     * @return self The current instance of this class.
     */
    public function withWebDriver(string $url = 'http://localhost:4444'): self
    {
        $this->httpClient = new WebDriverHttpClient($url);
        return $this;
    }

    /**
     * Sets the JsonWriter with the specified filename.
     *
     * @param string $filename The name of the file to write JSON data to.
     *
     * @return self Returns the current object instance.
     */
    public function withJsonWriter(string $filename): self
    {
        $this->writer = new JsonWriter($filename);
        return $this;
    }

    /**
     * Sets the CSVWriter writer with the specified filename headers and delimiter.
     *
     * @param string $filename The name of the CSV file.
     * @param array<string> $headers The headers of the CSV file.
     *
     * @return self Returns the current object instance.
     */
    public function withCSVWriter(
        string $filename,
        array $headers = [],
        string $delimiter = ','
    ): self {
        $this->writer = new CSVWriter($filename, $headers, $delimiter);
        return $this;
    }

    /**
     * Sets the DatabaseWriter writer.
     *
     * @param \PDO $pdo The PDO object to write to the database.
     * @param string $table The name of the table to write to.
     *
     * @return self Returns the current object instance.
     */
    public function withDatabaseWriter(\PDO $pdo, string $table): self
    {
        $this->writer = new DatabaseWriter($pdo, $table);
        return $this;
    }


    public function withMiddleware(Middleware $middleware): self
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

    /**
     * Create a new instance of the ScraPHP class.
     *
     * @return ScraPHP The initialized ScraPHP object.
     */
    public function create(): ScraPHP
    {

        $logger = $this->logger === null
            ? $this->createDefaultLogger('php://stdout')
            : $this->logger;

        $writer = $this->writer === null
            ? new JsonWriter('out.json')
            : $this->writer;

        $httpClient = $this->httpClient === null
            ? new GuzzleHttpClient()
            : $this->httpClient;

        $scraphp = new ScraPHP(
            httpClient: $httpClient,
            logger: $logger,
            writer: $writer
        );

        foreach($this->middlewares as $middleware) {
            $scraphp->addMidleware($middleware);
        }

        return $scraphp;
    }

    /**
     * Initializes the logger.
     *
     * @param  string  $logfile The path to the log file.
     *
     * @return LoggerInterface The initialized logger.
     *
     */
    private function createDefaultLogger(string $logfile): LoggerInterface
    {
        $logger = new Logger('SCRAPHP');
        $handler = new StreamHandler($logfile, Level::Debug);
        $formatter = new LineFormatter(
            "%datetime% %level_name%  %message% %context% %extra%\n",
            'Y-m-d H:i:s'
        );
        $handler->setFormatter($formatter);
        $logger->pushHandler($handler);

        return $logger;
    }
}
