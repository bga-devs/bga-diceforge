<?php

use PHPUnit\Framework\Attributes\DataProvider;
use Bga\Games\diceforge\Tests\DbTestCase;
use Bga\Games\diceforge\Tests\DbFixture;
use Bga\Games\diceforge\Entities\Player;
use Bga\Games\diceforge\ResourceChoiceHelper;
use DiceForge\Resources\ResourceChoice;

class ResourceChoiceHelperTest extends DbTestCase
{
    private ResourceChoiceHelper $helper;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([7, 42, 99, 77, 100, 1] as $id) {
            DbFixture::insertPlayer(self::$db, $id, new Player($id, "Player $id", "000000"));
        }

        $this->helper = new ResourceChoiceHelper(self::$db);
    }

    #[DataProvider('dbSetChoiceProvider')]
    public function testDbSetChoice(ResourceChoice $choice, int $expectedInt, int|string $playerId): void
    {
        $this->helper->dbSetChoice($playerId, $choice);

        $result = self::$db->getMysqli()->query(
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
        self::$db->getMysqli()->query(
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
        self::$db->getMysqli()->query(
            "UPDATE player SET ressource_choice = 99 WHERE player_id = 1"
        );

        $this->expectException(\ValueError::class);
        $this->helper->getRessourceChoice(1);
    }
}
