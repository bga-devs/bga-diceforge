<?php

namespace Bga\Games\diceforge\Framework\Entities;

readonly class StatDefinition
{
    public function __construct(
        public int $id,
        public string $name,
        public string $section, // 'table' or 'player'
    ) {
    }
}
