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
        // Clean up any leftover tables from previous test runs
        self::tearDown($db);

        $mysqli = $db->getMysqli();

        // ------------------------------------------------------------------
        // BGA standard tables (normally created by the framework, not in repo)
        // ------------------------------------------------------------------

        /*
        This Player table schema is different from the one in prod, as BGA does something
        funny around player_no.
        In prod player_no is the primary key and NOT NULL but Game.php executes SQL that inserts
        players without a player_no so I don't know how that could work without tampering with the
        SQL before execution.
        When running tests, we don't tamper with the SQL so player_no must have a default value
        and musn't be the primary key. Its value is set manually afterwards.
        */
        $mysqli->query('
            CREATE TABLE `player` (
                `player_no`         int(10) unsigned NOT NULL default 0,
                `player_id`         int(10) unsigned NOT NULL,
                `player_canal`      varchar(32) DEFAULT NULL,
                `player_name`       varchar(32) NOT NULL,
                `player_avatar`     varchar(10) DEFAULT NULL,
                `player_color`      varchar(6) NOT NULL,
                `player_score`      int NOT NULL DEFAULT 0,
                `player_score_aux`  int NOT NULL DEFAULT 0,
                `player_zombie`     tinyint(1) NOT NULL DEFAULT 0,
                `player_ai`         tinyint(1) NOT NULL DEFAULT 0,
                `player_eliminated` tinyint(1) NOT NULL DEFAULT 0,
                `player_next_notif_no` int unsigned NOT NULL DEFAULT 1,
                `player_enter_game` tinyint(1) NOT NULL DEFAULT 0,
                `player_over_time`  tinyint(1) NOT NULL DEFAULT 0,
                `player_is_multiactive` tinyint(1) NOT NULL DEFAULT 0,
                `player_start_reflexion_time` datetime DEFAULT NULL,
                `player_remaining_reflexion_time` int DEFAULT NULL,
                `player_beginner` varbinary(32) DEFAULT NULL,
                `player_state` int unsigned DEFAULT NULL,
                PRIMARY KEY (`player_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ');

        $mysqli->query('
            CREATE TABLE `global` (
                `global_id`    int unsigned NOT NULL,
                `global_value` bigint NOT NULL,
                PRIMARY KEY (`global_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ');

        $mysqli->query('
            CREATE TABLE `stats` (
                `stats_id`        int(10) unsigned NOT NULL AUTO_INCREMENT,
                `stats_type`      smallint unsigned NOT NULL,
                `stats_player_id` int(10) unsigned DEFAULT NULL,
                `stats_value`     float NOT NULL,
                PRIMARY KEY (`stats_id`),
                UNIQUE KEY `stats_table_id` (`stats_type`, `stats_player_id`),
                KEY `stats_player_id` (`stats_player_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ');

        $mysqli->query('
            CREATE TABLE `gamelog` (
                `gamelog_packet_id` int unsigned NOT NULL AUTO_INCREMENT,
                `gamelog_move_id` int unsigned DEFAULT NULL,
                `gamelog_private` tinyint(1) NOT NULL,
                `gamelog_time` datetime NOT NULL,
                `gamelog_player` int unsigned DEFAULT NULL,
                `gamelog_current_player` int unsigned DEFAULT NULL,
                `gamelog_notification` mediumtext NOT NULL,
                PRIMARY KEY (`gamelog_packet_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8
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
     * Clear all data from tables (not drop the tables themselves).
     * Used between test runs to reset data while keeping the schema.
     */
    public static function clearData(MysqliDb $db): void
    {
        $mysqli = $db->getMysqli();

        // Get all existing tables in the database
        $result = $mysqli->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE()");
        $tables = [];
        while ($row = $result->fetch_assoc()) {
            $tables[] = $row['TABLE_NAME'];
        }

        // Disable foreign key checks before truncating
        $mysqli->query('SET FOREIGN_KEY_CHECKS = 0');

        // Truncate only existing tables
        foreach ($tables as $table) {
            $mysqli->query("TRUNCATE TABLE `$table`");
        }

        // Re-enable foreign key checks
        $mysqli->query('SET FOREIGN_KEY_CHECKS = 1');
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
