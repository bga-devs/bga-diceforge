<?php

namespace Bga\Games\diceforge\Tests;

use PHPUnit\Framework\TestCase;
use Bga\Games\diceforge\Framework\Db\MysqliDb;

/**
 * Base test class that provides database initialization and cleanup.
 *
 * Initializes the database schema once on the first test run and reuses it
 * for all subsequent tests in the class, clearing data between each test.
 * This saves ~846ms per test by avoiding redundant schema creation.
 */
abstract class DbTestCase extends TestCase
{
    protected static MysqliDb $db;
    private static bool $dbInitialized = false;

    protected function setUp(): void
    {
        // Initialize database schema once on first test, reuse for subsequent tests
        if (!self::$dbInitialized) {
            self::$db = DbFixture::createDb();
            DbFixture::setUp(self::$db);
            self::$dbInitialized = true;
        }

        // Clear data to start fresh, but keep the schema
        DbFixture::clearData(self::$db);

        // Begin transaction for each test
        self::$db->getMysqli()->begin_transaction();
    }

    protected function tearDown(): void
    {
        // Commit to persist data for inspection after test
        self::$db->getMysqli()->commit();
    }
}
