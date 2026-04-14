<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Bga\Games\diceforge\Framework\Db\MysqliDb;
use Bga\Games\diceforge\Framework\Db\Repository;
use Bga\Games\diceforge\Tests\DbFixture;
use Bga\Games\diceforge\Tests\TestGame;
use Bga\Games\diceforge\Entities\Player;
use Bga\Games\diceforge\State\StateRegistry;

require_once __DIR__ . '/doubles/TestGame.php';
require_once __DIR__ . '/fixtures/PlayerProvider.php';

class GameBeginnerTest extends TestCase
{
    private MysqliDb $db;
    private Repository $playerRepository;

    protected function setUp(): void
    {
        $this->db = DbFixture::createDb();
        DbFixture::setUp($this->db);
        $this->playerRepository = new Repository(Player::class, $this->db);
    }

    public static function playerProvider(): array
    {
        return PlayerProvider::playerSets();
    }

    /**
     * @param list<Player> $players
     */
    #[DataProvider('playerProvider')]
    public function testGameSetup(array $players): void
    {
        $game = $this->initializeGame();

        $setupPlayers = [];
        foreach ($players as $player) {
            $setupPlayers[$player->id] = $player->toSetupArray();
        }

        # BGA Framework calls setupNewGame
        $result = $game->setupNewGame($setupPlayers);
        $expected_current_state = StateRegistry::BEGIN_TURN;
        $this->assertSame($expected_current_state->id(), $result);

        $playerNo = 1;
        foreach ($players as $player) {
            $player->playerNo = $playerNo;
            $playerNo++;
        }

        $this->assertPlayersInserted($players);
        $this->assertSame(1, $game->getMethodCallCount('reloadPlayersBasicInfos'));

        $expectedGameState = [
            'firstPlayerId' => $players[0]->id,
            'nbPlayers' => count($players),
            'isGameSetup' => 1,
            'currentPlayerNum' => 0,
            'turnCount' => 1,
        ];
        $this->assertGameStateValues($game, $expectedGameState);

        # Current state has one action, BGA Framwork calls it
        $nextActionMethod = new ReflectionMethod(TestGame::class, $expected_current_state->action());
        $nextActionMethod->invoke($game);
        #TODO assert stBeginTurn was called
        $expecedNotifyAllPlayersCalls = [
            [
                'type' => 'notifBeginTurn',
                'log' => 'Turn ${turn}/${totalTurns} begins',
                'args' => [
                    'totalTurns' => 9,
                    'turn' => 1,
                ],
            ],
        ];
        $this->assertSame($expecedNotifyAllPlayersCalls, $game->getNotifyAllPlayersCalls());

        $this->assertSame((string)$players[0]->id, $game->getActivePlayerId());

        $expectedGameState['currentPlayerNum'] = 1;
        $this->assertGameStateValues($game, $expectedGameState);


    }

    /**
     * @param list<Player> $players
     */
    private function assertPlayersInserted(array $players): void
    {
        $inserted = $this->playerRepository->findAll();
        $this->assertCount(count($players), $inserted, 'Player count mismatch');

        foreach ($players as $i => $player) {
            $this->assertSamePlayer($inserted[$i], $player);
        }
    }

    /**
     * Assert that two Player objects have the same properties.
     */
    private function assertSamePlayer(Player $actual, Player $expected): void
    {
        $this->assertSame($actual->id, $expected->id, "Player {$expected->id} ID mismatch");
        $this->assertSame($actual->player_no, $expected->player_no, "Player {$expected->id} player_no mismatch");
        $this->assertSame($actual->name, $expected->name, "Player {$expected->id} name mismatch");
        $this->assertSame($actual->score, $expected->score, "Player {$expected->id} score mismatch");
        $this->assertSame($actual->color, $expected->color, "Player {$expected->id} color mismatch");
    }

    /**
     * Assert multiple game state values at once.
     *
     * @param TestGame $game
     * @param array<string, mixed> $expectedValues Key-value pairs of state keys and expected values
     */
    private function assertGameStateValues(TestGame $game, array $expectedValues): void
    {
        foreach ($expectedValues as $key => $expectedValue) {
            $this->assertSame($expectedValue, $game->getGameStateValue($key), "Game state value mismatch for key '{$key}'");
        }
    }

    /**
     * Initialize a game instance with the state machine configuration.
     */
    private function initializeGame(): TestGame
    {
        $game = new TestGame($this->db);

        // Load state machine configuration
        require __DIR__ . '/../../states.inc.php';
        /** @var array $machinestates */
        $game->gamestate->_setStates($machinestates);

        return $game;
    }
}
