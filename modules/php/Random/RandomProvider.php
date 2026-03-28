<?php

namespace Bga\Games\diceforge\Random;

/**
 * Contract for randomness generation.
 * Allows tests to inject predictable random values.
 */
interface RandomProvider
{
    /**
     * Generate a random integer within the given range (inclusive).
     *
     * @param int $min minimum value (inclusive)
     * @param int $max maximum value (inclusive)
     * @return int random integer in range [$min, $max]
     */
    public function rand(int $min, int $max): int;
}
