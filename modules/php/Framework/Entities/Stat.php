<?php

namespace Bga\Games\diceforge\Framework\Entities;

use Bga\Games\diceforge\Framework\Orm\Column;
use Bga\Games\diceforge\Framework\Orm\Entity;
use Bga\Games\diceforge\Framework\Orm\Id;

#[Entity('stats')]
class Stat
{
    // This is NOT the 'id' field in stats.inc.php
    #[Id]
    #[Column('stats_id')]
    public int $id;

    // This IS the 'id' field in stats.inc.php
    #[Column('stats_type')]
    public int $type;

    #[Column('stats_player_id')]
    public ?int $playerId = null;

    #[Column('stats_value')]
    public float $value;

    public function __construct(int $id, int $type, ?int $playerId, float $value = 0.0)
    {
        $this->id    = $id;
        $this->type  = $type;
        $this->playerId = $playerId;
        $this->value = $value;
    }
}
