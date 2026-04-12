<?php

namespace Bga\Games\diceforge\Tests;

use Bga\Games\diceforge\Game;
use Bga\Games\diceforge\Framework\Db\Repository;
use Bga\Games\diceforge\Entities\Player;

/**
 * TestGame extends Game to expose protected methods for testing.
 */
class TestGame extends Game
{
    /** @var array<string, int> Track method call counts */
    private array $methodCallCounts = [
        'reloadPlayersBasicInfos' => 0,
    ];

    /**
     * Expose protected setupNewGame method for tests.
     */
    public function setupNewGame($players, $options = [])
    {
        $result = parent::setupNewGame($players, $options);

        // Assign player_no from 1 onwards, ordered by player_id
        $playerRepository = new Repository(Player::class, $this->db);
        $allPlayers = $playerRepository->findAll();

        $playerNo = 1;
        foreach ($allPlayers as $player) {
            $player->playerNo = $playerNo;
            $playerRepository->save($player);
            $playerNo++;
        }

        return $result;
    }

    public function reloadPlayersBasicInfos(): void
    {
        parent::reloadPlayersBasicInfos();
        $this->methodCallCounts['reloadPlayersBasicInfos']++;
    }

    /**
     * Get the call count for a tracked method.
     *
     * @throws \InvalidArgumentException if method is not tracked
     */
    public function getMethodCallCount(string $methodName): int
    {
        if (!array_key_exists($methodName, $this->methodCallCounts)) {
            throw new \InvalidArgumentException("Method '$methodName' is not tracked");
        }
        return $this->methodCallCounts[$methodName];
    }

    public function loadPlayersBasicInfos(): array
    {
        $playerRepository = new Repository(Player::class, $this->db);
        $dbPlayers = $playerRepository->findAll();

        $values = [];
        foreach ($dbPlayers as $player) {
            $values[$player->id] = [
                'player_name' => $player->name,
                'player_color' => $player->color,
                'player_table_order' => (string)$player->player_no,
                'player_canal' => '',
                'player_avatar' => '',
                'player_is_admin' => '0',
                'player_is_premium' => '0',
                'language' => 'en',
                'player_is_ai' => '0',
                'beginner' => '0',
            ];
        }

        return $values;
    }
}
