<?php

use Bga\Games\diceforge\Entities\Player;

class PlayerProvider
{
    public static function playerSets(): array
    {
        return [
            '2 players' => [
                [
                    new Player('Alice', 'D56F12'),
                    new Player('Bob', 'B6B525'),
                ],
            ],
        ];
    }
}
