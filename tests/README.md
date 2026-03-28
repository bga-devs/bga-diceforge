# Running the tests

## Prerequisites

Tests require a local MariaDB server and the PHP `mysqli` extension.  
Run the setup script **once** on a fresh machine (requires sudo):

```bash
sudo bash tests/setup-test-db.sh
```

This will:
1. Install `mariadb-server` and `php8.3-mysqli` if not already present
2. Start the MariaDB service
3. Create a `bga_test` database and a `bga_test` user (password: `bga_test`)

The script is idempotent — safe to run again at any time.

## Running the tests

```bash
./vendor/bin/phpunit
```

## Test database connection

`MysqliDb::createForTest()` connects with these defaults:

| Parameter | Value       |
|-----------|-------------|
| host      | `127.0.0.1` |
| user      | `bga_test`  |
| password  | `bga_test`  |
| database  | `bga_test`  |
| port      | `3306`      |

All parameters can be overridden via `createForTest()` arguments if needed.
