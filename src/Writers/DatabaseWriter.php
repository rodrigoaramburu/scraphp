<?php

declare(strict_types=1);

namespace ScraPHP\Writers;

use PDO;

final class DatabaseWriter implements WriterInterface
{

    public function __construct(
        private PDO $pdo,
        private string $table
    ){
    }

    public function data(array $data): void
    {

        $keys = array_keys($data);
        $columnsNames = implode(',', $keys);
        $valuesPlaceholder = implode(',', array_map( static fn($value) => ':'.$value, $keys));

        $sql = "INSERT INTO {$this->table}({$columnsNames}) VALUES({$valuesPlaceholder})";
        $stmt = $this->pdo->prepare($sql);

        foreach($keys as $key) {
        $stmt->bindValue(':'.$key, $data[$key]);
        }

        $stmt->execute();
    }

}
