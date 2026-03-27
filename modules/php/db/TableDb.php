<?php

namespace Bga\Games\diceforge\Db;

use Bga\GameFramework\Table;

class TableDb implements Db
{
    public function DbQuery(string $sql): null|\mysqli_result|bool
    {
        return Table::DbQuery($sql);
    }

    public function getUniqueValueFromDB(string $sql): mixed
    {
        return Table::getUniqueValueFromDB($sql);
    }

    public function getCollectionFromDB(string $sql, bool $bSingleValue = false): array
    {
        return Table::getCollectionFromDB($sql, $bSingleValue);
    }

    public function getObjectListFromDB(string $sql, bool $bUniqueValue = false): array
    {
        return Table::getObjectListFromDB($sql, $bUniqueValue);
    }

    public function getNonEmptyCollectionFromDB(string $sql): array
    {
        return Table::getNonEmptyCollectionFromDB($sql);
    }

    public function getObjectFromDB(string $sql): array
    {
        return Table::getObjectFromDB($sql);
    }

    public function mysql_fetch_assoc(\mysqli_result $result): array|false|null
    {
        return \mysql_fetch_assoc($result);
    }
}
