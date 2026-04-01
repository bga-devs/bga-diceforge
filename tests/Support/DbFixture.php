<?php

namespace Bga\Games\diceforge\Tests;

use Bga\Games\diceforge\Framework\Db\MysqliDb;
use Bga\Games\diceforge\Entities\Player;

/**
 * Bootstraps a fresh copy of the full game schema in the bga_test database.
 *
 * Call DbFixture::setUp() in PHPUnit's setUp() and DbFixture::tearDown() in
 * tearDown(). Every test run starts with empty, freshly-created tables.
 *
 * The BGA standard tables (player, global, stats, gamelog) are created with
 * a minimal but representative schema — the real columns are not in this repo.
 * All game-specific schema changes are sourced directly from dbmodel.sql so
 * that the fixture stays in sync automatically.
 */
class DbFixture
{
    private const DBMODEL = __DIR__ . '/../../dbmodel.sql';

    public static function createDb(): MysqliDb
    {
        return MysqliDb::createForTest();
    }

    public static function setUp(MysqliDb $db): void
    {
        $mysqli = $db->getMysqli();

        // Discover table names from dbmodel.sql so we can drop them too
        $gameTableNames = self::parseTableNames(self::DBMODEL);

        // Drop game tables first (may depend on player), then BGA base tables
        foreach ($gameTableNames as $table) {
            $mysqli->query("DROP TABLE IF EXISTS `$table`");
        }
        foreach (['stats', 'gamelog', 'global', 'player'] as $table) {
            $mysqli->query("DROP TABLE IF EXISTS `$table`");
        }

        // ------------------------------------------------------------------
        // BGA standard tables (normally created by the framework, not in repo)
        // ------------------------------------------------------------------

        $mysqli->query('
            CREATE TABLE `player` (
                `player_id`         int(10) unsigned NOT NULL,
                `player_score`      int(10)          NOT NULL DEFAULT 0,
                `player_score_aux`  int(10)          NOT NULL DEFAULT 0,
                `player_no`         int(10) unsigned NOT NULL DEFAULT 0,
                `player_name`       varchar(32)      NOT NULL DEFAULT \'\',
                `player_canal`      varchar(32)      NOT NULL DEFAULT \'\',
                `player_avatar`     varchar(32)      NOT NULL DEFAULT \'\',
                `player_color`      varchar(6)       NOT NULL DEFAULT \'000000\',
                `player_zombie`     tinyint(1)       NOT NULL DEFAULT 0,
                `player_ai`         tinyint(1)       NOT NULL DEFAULT 0,
                `player_eliminated` tinyint(1)       NOT NULL DEFAULT 0,
                PRIMARY KEY (`player_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ');

        $mysqli->query('
            CREATE TABLE `global` (
                `global_id`    int(10) unsigned NOT NULL,
                `global_value` int(10)          DEFAULT NULL,
                PRIMARY KEY (`global_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ');

        $mysqli->query('
            CREATE TABLE `stats` (
                `stats_type`      varchar(80)   NOT NULL,
                `stats_player_id` int(10)       NOT NULL DEFAULT 0,
                `stats_value`     varchar(1000) NOT NULL DEFAULT \'0\',
                PRIMARY KEY (`stats_type`, `stats_player_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ');

        $mysqli->query('
            CREATE TABLE `gamelog` (
                `gamelog_packet_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `gamelog_move_id`   int(10) unsigned NOT NULL DEFAULT 0,
                `gamelog_player_id` int(10)          NOT NULL DEFAULT 0,
                `gamelog_type`      varchar(32)      NOT NULL DEFAULT \'\',
                `gamelog_args`      text             NOT NULL,
                PRIMARY KEY (`gamelog_packet_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1
        ');

        // ------------------------------------------------------------------
        // Game-specific schema: execute dbmodel.sql directly
        // ------------------------------------------------------------------
        foreach (self::parseStatements(self::DBMODEL) as $stmt) {
            if ($mysqli->query($stmt) === false) {
                throw new \RuntimeException("DbFixture: SQL failed: {$mysqli->error}\n  Statement: $stmt");
            }
        }
    }

    public static function tearDown(MysqliDb $db): void
    {
        $mysqli = $db->getMysqli();
        $gameTableNames = self::parseTableNames(self::DBMODEL);
        foreach ($gameTableNames as $table) {
            $mysqli->query("DROP TABLE IF EXISTS `$table`");
        }
        foreach (['stats', 'gamelog', 'global', 'player'] as $table) {
            $mysqli->query("DROP TABLE IF EXISTS `$table`");
        }
    }

    /**
     * Insert a minimal player row using a Player object.
     */
    public static function insertPlayer(MysqliDb $db, int $playerId, Player $player): void
    {
        $defaults = [
            'player_score'    => 0,
            'player_score_aux' => 0,
            'player_no'       => $playerId,
            'player_name'     => $player->name,
            'player_color'    => $player->color,
        ];
        $cols = $defaults;

        $setCols = implode(', ', array_map(fn ($k) => "`$k`", array_keys($cols)));
        $setVals = implode(', ', array_map(fn ($v) => "'" . $db->getMysqli()->real_escape_string((string) $v) . "'", array_values($cols)));

        $db->getMysqli()->query(
            "INSERT INTO `player` (`player_id`, $setCols) VALUES ($playerId, $setVals)"
        );
    }

    // -------------------------------------------------------------------------
    // dbmodel.sql parser helpers
    // -------------------------------------------------------------------------

    /**
     * Parse dbmodel.sql and return each executable SQL statement (comments and
     * blank lines stripped, statements split on semicolons).
     *
     * @return string[]
     */
    private static function parseStatements(string $file): array
    {
        if (!file_exists($file)) {
            throw new \RuntimeException("DbFixture: file not found: $file\nCheck that the path constant is correct after any reorganization.");
        }
        $raw = file_get_contents($file);

        // Strip single-line comments (-- ...) but preserve the newline
        $raw = preg_replace('/--[^\n]*/u', '', $raw);

        $statements = [];
        foreach (explode(';', $raw) as $part) {
            $stmt = trim($part);
            if ($stmt !== '') {
                $statements[] = $stmt;
            }
        }
        return $statements;
    }

    /**
     * Return every table name that appears in a CREATE TABLE statement in the
     * given SQL file (used to build the DROP list).
     *
     * @return string[]
     */
    private static function parseTableNames(string $file): array
    {
        if (!file_exists($file)) {
            throw new \RuntimeException("DbFixture: file not found: $file\nCheck that the path constant is correct after any reorganization.");
        }
        $raw = file_get_contents($file);
        preg_match_all('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?(\w+)`?/ui', $raw, $matches);
        return array_unique($matches[1]);
    }
}
