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

final class ScraPHPBuilder
{
    private ?HttpClient $httpClient = null;

    private ?LoggerInterface $logger = null;

    private ?Writer $writer = null;

    private int $retryCount = 3;

    private int $retryTime = 30;

    /**
     * Sets the HttpClient for the object and returns itself.
     *
     * @param  HttpClient  $httpClient The HttpClient to be set.
     * @return self The updated object.
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
     * Sets the writer for the object.
     *
     * @param  Writer  $writer The writer object to be set.
     * @return self The modified object with the new writer.
     */
    public function withWriter(Writer $writer): self
    {
        $this->writer = $writer;

        return $this;
    }

    /**
     * Sets the retry count.
     *
     * @param  int  $retryCount The number of times the function should be retried.
     */
    public function withRetryCount(int $retryCount): self
    {
        $this->retryCount = $retryCount;

        return $this;
    }

    /**
     * Sets the retry time.
     *
     * @param  int  $retryTime The retry time in milliseconds.
     * @return self The current instance of the class.
     */
    public function withRetryTime(int $retryTime): self
    {
        $this->retryTime = $retryTime;

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
     * Sets the writer of the object to an instance of JsonWriter with the specified filename.
     *
     * @param string $filename The name of the file to write JSON data to.
     * @return self Returns the modified object with the new writer.
     */
    public function withJsonWriter(string $filename): self
    {
        $this->writer = new JsonWriter($filename);
        return $this;
    }

    /**
     * Sets the CSV writer for the object and returns the object itself.
     *
     * @param string $filename The name of the CSV file.
     * @return self The object with the CSV writer set.
     */
    public function withCSVWriter(
        string $filename,
        array $headers = [],
        string $delimiter = ','
    ): self {
        $this->writer = new CSVWriter($filename, $headers, $delimiter);
        return $this;
    }

    public function withDatabaseWriter(\PDO $pdo, string $table): self
    {
        $this->writer = new DatabaseWriter($pdo, $table);
        return $this;
    }

    /**
     * Create a new instance of the ScraPHP class.
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

        return new ScraPHP(
            httpClient: $httpClient,
            logger: $logger,
            writer: $writer,
            retryCount: $this->retryCount,
            retryTime: $this->retryTime
        );
    }

    /**
     * Initializes the logger.
     *
     * @param  string  $logfile The path to the log file.
     * @return LoggerInterface The initialized logger.
     *
     * @throws Exception If there is an error initializing the logger.
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
