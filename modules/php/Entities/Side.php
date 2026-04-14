<?php

namespace Bga\Games\diceforge\Entities;

use Bga\Games\diceforge\Framework\Orm\Column;
use Bga\Games\diceforge\Framework\Orm\Entity;
use Bga\Games\diceforge\Framework\Orm\Id;

#[Entity('sides')]
class Side
{
    #[Id]
    #[Column('card_id')]
    public int $id;

    #[Column('card_type')]
    public string $type;

    #[Column('card_type_arg')]
    public int $played = 0;

    #[Column('card_location')]
    public string $location;

    /**
     * Location-dependent position or index:
     * - If discarded: always 0
     * - If in a pool: 1-based position within the sub-pool (max 4 with 4 players)
     * - If on a die: 0-based index (0 to 5)
     */
    #[Column('card_location_arg')]
    public int $positionOrIndexInLocation = 0;

    public function __construct(int $id, string $type, string $location)
    {
        $this->id    = $id;
        $this->type  = $type;
        $this->location = $location;
    }
}
