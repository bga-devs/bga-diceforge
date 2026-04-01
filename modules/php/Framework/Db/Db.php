<?php

namespace Bga\Games\diceforge\Framework\Db;

/**
 * Thin wrapper over BGA's Table static database methods.
 *
 * Method names and signatures intentionally mirror Table's statics so that
 * call sites read identically to the BGA docs and mocks are trivial to write.
 *
 * Why this exists: Table's methods are `final public static`, so they cannot
 * be directly mocked or injected. This interface solves that without adding
 * any translation layer.
 */
interface Db
{
    public function DbQuery(string $sql): null|\mysqli_result|bool;

    public function getUniqueValueFromDB(string $sql): mixed;

    public function getCollectionFromDB(string $sql, bool $bSingleValue = false): array;

    public function getObjectListFromDB(string $sql, bool $bUniqueValue = false): array;

    public function getNonEmptyCollectionFromDB(string $sql): array;

    public function getObjectFromDB(string $sql): array;

    public function mysql_fetch_assoc(\mysqli_result $result): array|false|null;

    public function escape(string $value): string;
}
