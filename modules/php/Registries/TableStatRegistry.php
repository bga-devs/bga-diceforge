<?php

namespace Bga\Games\diceforge\Registries;

use Bga\Games\diceforge\Framework\Entities\StatDefinition;

enum TableStatRegistry
{
    case turns_number;

    public function definition(): StatDefinition
    {
        return match ($this) {
            self::turns_number => new StatDefinition(
                id: 10,
                name: 'Number of turns',
                section: 'table',
            ),
        };
    }

    public function id(): int
    {
        return $this->definition()->id;
    }

    public static function fromName(string $name): self
    {
        foreach (self::cases() as $case) {
            if ($case->name === $name) {
                return $case;
            }
        }
        throw new \ValueError("Unknown table stat: '{$name}'");
    }
}
