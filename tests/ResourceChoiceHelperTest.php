<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Bga\Games\diceforge\ResourceChoiceDb;
use Bga\Games\diceforge\ResourceChoiceHelper;
use DiceForge\Resources\ResourceChoice;

class ResourceChoiceHelperTest extends TestCase
{
    private ResourceChoiceDb&\PHPUnit\Framework\MockObject\MockObject $db;
    private ResourceChoiceHelper $helper;

    protected function setUp(): void
    {
        $this->db = $this->createMock(ResourceChoiceDb::class);
        $this->helper = new ResourceChoiceHelper($this->db);
    }

    #[DataProvider('dbSetChoiceProvider')]
    public function testDbSetChoice(ResourceChoice $choice, int $expectedInt, int|string $playerId): void
    {
        $this->db->expects($this->once())
            ->method('executeQuery')
            ->with("UPDATE player SET ressource_choice = $expectedInt WHERE player_id = $playerId");

        $this->helper->dbSetChoice($playerId, $choice);
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
        $this->db->expects($this->once())
            ->method('getUniqueValue')
            ->with("SELECT ressource_choice FROM player WHERE player_id = $playerId")
            ->willReturn($dbValue);

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
        $this->db->method('getUniqueValue')->willReturn(99);

        $this->expectException(\ValueError::class);
        $this->helper->getRessourceChoice(1);
    }
}
