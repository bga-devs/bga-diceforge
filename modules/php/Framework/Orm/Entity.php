<?php

namespace Bga\Games\diceforge\Framework\Orm;

/**
 * Marks a class as a database entity and declares its table name.
 *
 * Usage:
 *   #[Entity('player')]
 *   class Player { ... }
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class Entity
{
    public function __construct(public readonly string $table)
    {
    }
}
