<?php

namespace Bga\Games\diceforge\Framework\Orm;

/**
 * Marks a property as the primary key of the entity.
 * Must be combined with #[Column].
 *
 * Usage:
 *   #[Id]
 *   #[Column('player_id')]
 *   public int $id = 0;
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class Id
{
}
