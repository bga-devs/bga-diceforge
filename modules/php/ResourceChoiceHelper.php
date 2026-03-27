<?php

namespace Bga\Games\diceforge;

use DiceForge\Resources\ResourceChoice;
use Bga\Games\diceforge\Db\Db;

require_once __DIR__ . '/db/Db.php';

/**
 * Handles reading and writing the `ressource_choice` column on the `player` table.
 */
class ResourceChoiceHelper
{
    public function __construct(private readonly Db $db)
    {
    }

    public function dbSetChoice(int|string $player_id, ResourceChoice $value): void
    {
        $sql =
            'UPDATE player SET ressource_choice = ' .
            $value->value .
            ' WHERE player_id = ' .
            $player_id;
        $this->db->DbQuery($sql);
    }

    public function getRessourceChoice(int|string $player_id): ResourceChoice
    {
        $sql = 'SELECT ressource_choice FROM player';

        if ($player_id !== null) {
            $sql .= ' WHERE player_id = ' . $player_id;
        }

        return ResourceChoice::from((int) $this->db->getUniqueValueFromDB($sql));
    }
}
