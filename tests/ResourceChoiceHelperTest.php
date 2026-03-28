<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Bga\Games\diceforge\Db\MysqliDb;
use Bga\Games\diceforge\Tests\DbFixture;
use Bga\Games\diceforge\ResourceChoiceHelper;
use DiceForge\Resources\ResourceChoice;

class ResourceChoiceHelperTest extends TestCase
{
    private MysqliDb $db;
    private ResourceChoiceHelper $helper;

    protected function setUp(): void
    {
        $this->db = DbFixture::createDb();
        DbFixture::setUp($this->db);

        foreach ([7, 42, 99, 77, 100, 1] as $id) {
            DbFixture::insertPlayer($this->db, $id);
        }

        $this->helper = new ResourceChoiceHelper($this->db);
    }

    protected function tearDown(): void
    {
        DbFixture::tearDown($this->db);
    }

    #[DataProvider('dbSetChoiceProvider')]
    public function testDbSetChoice(ResourceChoice $choice, int $expectedInt, int|string $playerId): void
    {
        $this->helper->dbSetChoice($playerId, $choice);

        $result = $this->db->getMysqli()->query(
            "SELECT ressource_choice FROM player WHERE player_id = $playerId"
        );
        $row = $result->fetch_assoc();
        $this->assertSame($expectedInt, (int) $row['ressource_choice']);
    }

    public static function dbSetChoiceProvider(): array
    {
        return [
            'nothing_todo with int player'    => [ResourceChoice::RC_NOTHING_TODO, -1, 7],
            'ressource with int player'       => [ResourceChoice::RC_RESSOURCE, 1, 42],
            'forgeship with string player'    => [ResourceChoice::RC_FORGESHIP, 2, '99'],
        ];
    }

    #[DataProvider('getRessourceChoiceProvider')]
    public function testGetRessourceChoice(int $dbValue, ResourceChoice $expected, int|string $playerId): void
    {
        $this->db->getMysqli()->query(
            "UPDATE player SET ressource_choice = $dbValue WHERE player_id = $playerId"
        );

        $result = $this->helper->getRessourceChoice($playerId);

        $this->assertSame($expected, $result);
    }

    public static function getRessourceChoiceProvider(): array
    {
        return [
            'nothing_todo with int player'    => [-1, ResourceChoice::RC_NOTHING_TODO, 42],
            'maze with string player'         => [5, ResourceChoice::RC_MAZE, '77'],
            'misfortune with int player'      => [6, ResourceChoice::RC_MISFORTUNE, 100],
        ];
    }

    public function testGetRessourceChoiceInvalidIntThrows(): void
    {
        $this->db->getMysqli()->query(
            "UPDATE player SET ressource_choice = 99 WHERE player_id = 1"
        );

        $this->expectException(\ValueError::class);
        $this->helper->getRessourceChoice(1);
    }
}
