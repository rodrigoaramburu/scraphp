<?php

declare(strict_types=1);

namespace ScraPHP\Writers;

use Exception;

final class CSVWriter implements Writer
{
    private mixed $file;

    private string $filename;
    /**
     * The header to include in the file.
     *
     * @var array<string>
     */
    private array $header;

    private string $separator;

    /**
     * Constructs a new instance of the class.
     *
     * @param  string  $filename The name of the file to work with.
     * @param  array<string>  $header The header to include in the file.
     * @param  string  $separator The separator to use in the file.
     */
    public function __construct(string $filename, array $header = [], string $separator = ',')
    {
        $this->filename = $filename;
        $this->header = $header;
        $this->separator = $separator;
        if (file_exists($filename)) {
            $this->openFileToAppend($filename);

            return;
        }
        $this->createFile($filename);
    }

    /**
     * Writes an array of data to the file.
     *
     * @param  array<string,string>  $data The data to be written.
     */
    public function write(array $data): void
    {
        if ($this->header !== []) {
            $orderedData = $this->orderData($data);
        } else {
            $orderedData = $data;
        }

        fwrite($this->file, "\n".implode($this->separator, $orderedData));
    }

    /**
     * Checks if a record exists in the file based on a search criteria.
     *
     * @param  array<string, string>  $search The search criteria to match against the records in the file.
     * @return bool Returns true if a record matching the search criteria is found, false otherwise.
     */
    public function exists(array $search): bool
    {
        rewind($this->file);
        fgets($this->file);
        while (! feof($this->file)) {
            $line = trim(fgets($this->file), "\n");
            if ($line === '') {
                continue;
            }

            $data = explode($this->separator, $line);

            if ($this->matchCriteria($data, $search)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determines if the given data matches the search criteria.
     *
     * @param  array<string>  $data The data to be checked.
     * @param  array<string, string>  $search The search criteria.
     * @return bool Returns true if the data matches the search criteria, and false otherwise.
     */
    private function matchCriteria(array $data, array $search): bool
    {
        $flag = true;

        foreach ($search as $key => $value) {
            $keyPosition = array_search($key, $this->header);
            if ($data[$keyPosition] !== strval($value)) {
                $flag = false;
                break;
            }
        }

        return $flag;
    }

    /**
     * Opens a file in append mode and assigns the file handle to the object property.
     *
     * @param  string  $filename The name of the file to open.
     */
    private function openFileToAppend(string $filename): void
    {
        $this->file = fopen($filename, 'a+');

        if (implode(',', $this->header) !== trim(fgets($this->file))) {
            throw new Exception("File {$filename} already exists with different header");
        }
    }

    /**
     * Creates a file with the given filename and writes the header to it.
     *
     * @param  string  $filename The name of the file to be created.
     */
    private function createFile(string $filename): void
    {
        $this->file = fopen($filename, 'w+');
        fwrite($this->file, implode($this->separator, $this->header));
    }

    /**
     * Orders the data based on the header.
     *
     * @param  array<string>  $data The data to be ordered.
     * @return array<string> The ordered data.
     */
    public function orderData(array $data): array
    {
        $orderedData = [];
        foreach ($this->header as $key) {
            $orderedData[] = $data[$key] ?? '';
        }

        return $orderedData;
    }

    /**
     * Destructor method for the class.
     */
    public function __destruct()
    {
        fclose($this->file);
    }

    /**
     * Returns the filename.
     *
     * @return string The filename.
     */
    public function filename(): string
    {
        return $this->filename;
    }

    /**
     * Gets the headers array of the file.
     *
     * @return array The header array.
     */
    public function header(): array
    {
        return $this->header;
    }

    /**
     * Gets the separator used.
     *
     * @return string The separator.
     */
    public function separator(): string
    {
        return $this->separator;
    }
}
