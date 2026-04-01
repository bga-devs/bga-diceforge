<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Bga\Games\diceforge\Framework\Db\MysqliDb;
use Bga\Games\diceforge\Framework\Db\Repository;
use Bga\Games\diceforge\Tests\DbFixture;
use Bga\Games\diceforge\Tests\TestGame;
use Bga\Games\diceforge\Entities\Player;

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

    protected function tearDown(): void
    {
        DbFixture::tearDown($this->db);
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
        $game = new TestGame($this->db);

        // Convert sequential player list to ID-keyed format expected by setupNewGame (starting from ID 1)
        $setupPlayers = array_combine(
            range(1, count($players)),
            array_map(fn (Player $p): array => $p->toSetupArray(), $players)
        );
        $game->setupNewGame($setupPlayers);
        $this->assertPlayersInserted($players);
    }

    /**
     * @param list<Player> $players
     */
    private function assertPlayersInserted(array $players): void
    {
        $inserted = $this->playerRepository->findAll();
        $this->assertCount(count($players), $inserted, 'Player count mismatch');

        // Verify player IDs are incremental starting from 1 and names match
        for ($id = 1; $id <= count($players); $id++) {
            $player = $players[$id - 1];
            $player->id = $id; // Expect ID to be assigned by DB starting from 1
            $found = $inserted[$id - 1];
            $this->assertSamePlayer($found, $player);
        }
    }

    /**
     * Assert that two Player objects have the same properties.
     */
    private function assertSamePlayer(Player $actual, Player $expected): void
    {
        $this->assertSame($actual->id, $expected->id, "Player {$expected->id} ID mismatch");
        $this->assertSame($actual->name, $expected->name, "Player {$expected->id} name mismatch");
        $this->assertSame($actual->score, $expected->score, "Player {$expected->id} score mismatch");
        $this->assertSame($actual->color, $expected->color, "Player {$expected->id} color mismatch");
    }
}
