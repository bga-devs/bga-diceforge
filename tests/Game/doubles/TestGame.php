<?php

namespace Bga\Games\diceforge\Tests;

use Bga\Games\diceforge\Game;

/**
 * TestGame extends Game to expose protected methods for testing.
 */
class TestGame extends Game
{
    /**
     * Expose protected setupNewGame method for tests.
     */
    public function setupNewGame($players, $options = [])
    {
        return parent::setupNewGame($players, $options);
    }

    /**
     * Expose protected getAllDatas method for tests.
     */
    public function getAllDatas()
    {
        return parent::getAllDatas();
    }
}
