<?php

namespace Bga\Games\diceforge\Tests;

use Bga\Games\diceforge\Game;
use Bga\Games\diceforge\Framework\Db\Repository;
use Bga\Games\diceforge\Entities\Player;
use Bga\Games\diceforge\Framework\Entities\Stat;
use Bga\Games\diceforge\Registries\TableStatRegistry;
use Bga\Games\diceforge\Registries\PlayerStatRegistry;

/**
 * TestGame extends Game to expose protected methods for testing.
 */
class TestGame extends Game
{
    /** @var array<string, int> Track method call counts */
    private array $methodCallCounts = [
        'reloadPlayersBasicInfos' => 0,
    ];

    /** @var array<int, array{type: string, log: string, args: array}> Track notifyAllPlayers calls */
    private array $notifyAllPlayersCalls = [];

    /**
     * Expose protected setupNewGame method for tests.
     */
    public function setupNewGame($players, $options = []): int
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

        // Seed all stats with default value 0
        $statRepository = new Repository(Stat::class, $this->db);

        # id is auto-increment and the value 0 means "please auto-assign"
        foreach (TableStatRegistry::cases() as $stat) {
            $statRepository->save(new Stat(0, $stat->id(), null, 0));
        }

        foreach ($allPlayers as $player) {
            foreach (PlayerStatRegistry::cases() as $stat) {
                $statRepository->save(new Stat(0, $stat->id(), $player->id, 0));
            }
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

    /**
     * Override notifyAllPlayers to track all calls.
     */
    public function notifyAllPlayers(string $notificationType, string $notificationLog, array $notificationArgs): void
    {
        parent::notifyAllPlayers($notificationType, $notificationLog, $notificationArgs);

        $this->notifyAllPlayersCalls[] = [
            'type' => $notificationType,
            'log' => $notificationLog,
            'args' => $notificationArgs,
        ];
    }

    /**
     * Get all notifyAllPlayers calls made during the test.
     *
     * @return array<int, array{type: string, log: string, args: array}>
     */
    public function getNotifyAllPlayersCalls(): array
    {
        return $this->notifyAllPlayersCalls;
    }

    /**
     * Override incStat to use Repository and Stats entity for testing.
     * Increments a stat value in the database using the Stats entity.
     * Creates the stat if it doesn't exist.
     */
    public function incStat(int $inc, string $name, ?int $playerId = null, bool $bDoNotLoop = false): void
    {
        $statRepository = new Repository(Stat::class, $this->db);

        $typeId = $playerId === null
            ? TableStatRegistry::fromName($name)->id()
            : PlayerStatRegistry::fromName($name)->id();

        $stat = $statRepository->findBy(['stats_type' => $typeId, 'stats_player_id' => $playerId]);
        $stat->value += $inc;
        $statRepository->save($stat);
    }
}
