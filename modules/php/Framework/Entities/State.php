<?php

namespace Bga\Games\diceforge\Framework\Entities;

use Bga\GameFramework\StateType;

readonly class State
{
    public function __construct(
        public int $id,
        public string $name,
        public StateType $type,
        public string $description = '',
        public string $descriptionMyTurn = '',
        public ?string $action = null,
        public array $possibleActions = [],
        public bool $updateGameProgression = false,
    ) {
    }
}
