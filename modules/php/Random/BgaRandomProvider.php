<?php

namespace Bga\Games\diceforge\Random;

/**
 * Production implementation of RandomProvider.
 * Wraps the BGA framework's bga_rand() function.
 */
class BgaRandomProvider implements RandomProvider
{
    public function rand(int $min, int $max): int
    {
        return bga_rand($min, $max);
    }
}
