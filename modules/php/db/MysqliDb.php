<?php

namespace Bga\Games\diceforge\Db;

/**
 * mysqli-backed implementation of Db for local testing.
 *
 * Connects to a real MariaDB/MySQL server so all SQL — including
 * mysql_fetch_assoc — works exactly as it does on the BGA platform.
 *
 * Run tests/setup-test-db.sh (once, with sudo) before using this class.
 *
 * Default connection targets the test database created by that script:
 *   host=127.0.0.1  user=bga_test  password=bga_test  db=bga_test
 */
class MysqliDb implements Db
{
    public function __construct(private readonly \mysqli $mysqli)
    {
    }

    public static function createForTest(
        string $host = '127.0.0.1',
        string $user = 'bga_test',
        string $password = 'bga_test',
        string $database = 'bga_test',
        int $port = 3306,
    ): self {
        $mysqli = new \mysqli($host, $user, $password, $database, $port);
        if ($mysqli->connect_error) {
            throw new \RuntimeException("MysqliDb: connection failed: " . $mysqli->connect_error);
        }
        $mysqli->set_charset('utf8');
        return new self($mysqli);
    }

    public function getMysqli(): \mysqli
    {
        return $this->mysqli;
    }

    // -------------------------------------------------------------------------
    // Interface implementation
    // -------------------------------------------------------------------------

    public function DbQuery(string $sql): null|\mysqli_result|bool
    {
        $result = $this->mysqli->query($sql);
        if ($result === false) {
            throw new \BgaSystemException("DbQuery failed: " . $this->mysqli->error . " | SQL: $sql");
        }
        return $result;
    }

    public function getUniqueValueFromDB(string $sql): mixed
    {
        $result = $this->DbQuery($sql);
        if (!($result instanceof \mysqli_result)) {
            return null;
        }
        $rows = $result->fetch_all(MYSQLI_NUM);
        $result->free();
        if (count($rows) > 1) {
            throw new \BgaSystemException("getUniqueValueFromDB: query returned more than 1 row");
        }
        return $rows[0][0] ?? null;
    }

    public function getCollectionFromDB(string $sql, bool $bSingleValue = false): array
    {
        $result = $this->DbQuery($sql);
        if (!($result instanceof \mysqli_result)) {
            return [];
        }
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        $collection = [];
        foreach ($rows as $row) {
            $values = array_values($row);
            $key = $values[0];
            $collection[$key] = $bSingleValue ? ($values[1] ?? null) : $row;
        }
        return $collection;
    }

    public function getObjectListFromDB(string $sql, bool $bUniqueValue = false): array
    {
        $result = $this->DbQuery($sql);
        if (!($result instanceof \mysqli_result)) {
            return [];
        }
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        if (!$bUniqueValue) {
            return $rows;
        }
        return array_map(fn (array $row) => reset($row), $rows);
    }

    public function getNonEmptyCollectionFromDB(string $sql): array
    {
        $result = $this->getCollectionFromDB($sql);
        if (empty($result)) {
            throw new \BgaSystemException("getNonEmptyCollectionFromDB: empty collection");
        }
        return $result;
    }

    public function getObjectFromDB(string $sql): array
    {
        $result = $this->DbQuery($sql);
        if (!($result instanceof \mysqli_result)) {
            return [];
        }
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        if (count($rows) > 1) {
            throw new \BgaSystemException("getObjectFromDB: query returned more than 1 row");
        }
        return $rows[0] ?? [];
    }

    public function mysql_fetch_assoc(\mysqli_result $result): array|false|null
    {
        return $result->fetch_assoc();
    }
}
