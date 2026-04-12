<?php

use Bga\Games\diceforge\Entities\Player;

class PlayerProvider
{
    public static function playerSets(): array
    {
        return [
            '2 players' => [
                [
                    new Player(42, 'Alice', 'D56F12'),
                    new Player(1337, 'Bob', 'B6B525'),
                ],
            ],
        ];
    }
}
