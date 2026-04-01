<?php

namespace Bga\Games\diceforge\Entities;

use Bga\Games\diceforge\Framework\Orm\Column;
use Bga\Games\diceforge\Framework\Orm\Entity;
use Bga\Games\diceforge\Framework\Orm\Id;

#[Entity('player')]
class Player
{
    #[Id]
    #[Column('player_id')]
    public int $id = 0;

    #[Column('player_score')]
    public int $score = 0;

    #[Column('player_name')]
    public string $name;

    #[Column('player_color')]
    public string $color;

    public function __construct(string $name, string $color)
    {
        $this->name  = $name;
        $this->color = $color;
    }

    /**
     * Returns the player entry as expected by setupNewGame()'s $players array.
     */
    public function toSetupArray(): array
    {
        return [
            'player_canal'  => '',
            'player_name'   => $this->name,
            'player_avatar' => '',
        ];
    }
}
