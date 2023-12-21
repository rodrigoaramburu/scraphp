<?php

declare(strict_types=1);

namespace ScraPHP\Writers;

final class DatabaseWriter implements Writer
{
    /**
     * Constructs a new instance of the class.
     *
     * @param  \PDO  $pdo The PDO object.
     * @param  string  $table The name of the table.
     */
    public function __construct(
        private \PDO $pdo,
        private string $table
    ) {
    }

    /**
     * Writes data to the database.
     *
     * @param  array<string,mixed>  $data The data to write.
     */
    public function write(array $data): void
    {
        $keys = array_keys($data);
        $columnsNames = implode(',', $keys);
        $valuesPlaceholder = implode(',', array_map(static fn ($value) => ':'.$value, $keys));

        $sql = "INSERT INTO {$this->table}({$columnsNames}) VALUES({$valuesPlaceholder})";
        $stmt = $this->pdo->prepare($sql);

        foreach ($keys as $key) {
            $stmt->bindValue(':'.$key, $data[$key]);
        }

        $stmt->execute();
    }

    /**
     * Checks if a record exists in the database based on the given search criteria.
     *
     * @param  array<string,mixed>  $search The search criteria to use for the query.
     *
     * @return bool Returns true if a record exists, false otherwise.
     */
    public function exists(array $search): bool
    {
        $query = array_map(static fn ($value, $key) => "{$key} = :{$key}", $search, array_keys($search));
        $stmp = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE ".implode(' AND ', $query));
        $stmp->execute($search);

        $data = $stmp->fetchAll(\PDO::FETCH_ASSOC);

        return count($data) > 0;
    }
}
