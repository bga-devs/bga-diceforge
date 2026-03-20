<?php

namespace Bga\Games\diceforge;

use DiceForge\Resources\ResourceChoice;

/**
 * Abstraction over the two Table DB methods needed by ResourceChoiceHelper.
 *
 * Why this interface exists: BGA's Table methods (DbQuery, getUniqueValueFromDB)
 * are declared `final public static`, so they cannot directly satisfy an interface.
 * This interface allows ResourceChoiceHelper to be fully testable via PHPUnit mocks.
 */
interface ResourceChoiceDb
{
    public function executeQuery(string $sql): void;
    public function getUniqueValue(string $sql): mixed;
}

/**
 * Handles reading and writing the `ressource_choice` column on the `player` table.
 */
class ResourceChoiceHelper
{
    public function __construct(private ResourceChoiceDb $db)
    {
    }

    public function dbSetChoice(int|string $player_id, ResourceChoice $value): void
    {
        $sql =
            'UPDATE player SET ressource_choice = ' .
            $value->value .
            ' WHERE player_id = ' .
            $player_id;
        $this->db->executeQuery($sql);
    }

    public function getRessourceChoice(int|string $player_id): ResourceChoice
    {
        $sql = 'SELECT ressource_choice FROM player';

        if ($player_id !== null) {
            $sql .= ' WHERE player_id = ' . $player_id;
        }

        return ResourceChoice::from((int) $this->db->getUniqueValue($sql));
    }
}
