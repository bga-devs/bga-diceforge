<?php

namespace Bga\Games\diceforge\Framework\Orm;

/**
 * Maps a property to a database column.
 *
 * Usage:
 *   #[Column('player_name')]
 *   public string $name = '';
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class Column
{
    public function __construct(public readonly string $name)
    {
    }
}
