<?php

declare(strict_types=1);

namespace ScraPHP\Writers;

final class JsonWriter implements Writer
{
    private string $filename;

    /**
     * Constructs a new instance of the class.
     *
     * @param  string  $filename The name of the file to be used.
     */
    public function __construct(string $filename)
    {
        $this->filename = $filename;

        if (! file_exists($filename)) {
            file_put_contents($this->filename, '[]');
        }
    }

    /**
     * Writes data to a file in JSON format.
     *
     * @param  array  $data The data to be written.
     */
    public function write(array $data): void
    {
        $json = file_get_contents($this->filename);
        $jsonData = json_decode($json, true);
        $jsonData[] = $data;
        file_put_contents($this->filename, json_encode($jsonData, JSON_PRETTY_PRINT));
    }

    /**
     * Checks if the given search criteria exists in the JSON data.
     *
     * @param  array  $search The search criteria to check.
     * @return bool Returns true if the search criteria exists in the JSON data, false otherwise.
     */
    public function exists(array $search): bool
    {
        $json = file_get_contents($this->filename);
        $jsonData = json_decode($json, true);

        foreach ($jsonData as $data) {
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

            if ($data[$key] !== $value) {
                $flag = false;
                break;
            }
        }

        return $flag;
    }


    public function filename(): string
    {
        return $this->filename;
    }
}
