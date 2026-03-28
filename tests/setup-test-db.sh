#!/usr/bin/env bash
# ----------------------------------------------------------------------------
# setup-test-db.sh
#
# One-time setup for running PHPUnit tests against a local MariaDB/MySQL server.
# Safe to run multiple times (all statements are idempotent).
#
# Requirements: mariadb-server (or mysql-server) + php8.3-mysqli
#
# Usage:
#   sudo bash tests/setup-test-db.sh
#
# After running this script, the tests/MysqliDb.php implementation can connect
# with:
#   host     = 127.0.0.1
#   user     = bga_test
#   password = bga_test
#   database = bga_test
# ----------------------------------------------------------------------------

set -euo pipefail

# ---------- install packages if missing -------------------------------------
install_if_missing() {
    local pkg="$1"
    if ! dpkg -s "$pkg" &>/dev/null; then
        echo "[setup-test-db] Installing $pkg …"
        apt-get install -y "$pkg"
    else
        echo "[setup-test-db] $pkg already installed"
    fi
}

if [[ $EUID -ne 0 ]]; then
    echo "ERROR: Please run this script with sudo." >&2
    exit 1
fi

# Update package list before installing
apt-get update

install_if_missing mariadb-server
install_if_missing php8.3-mysqli

# ---------- ensure server is running ----------------------------------------
if ! systemctl is-active --quiet mariadb 2>/dev/null && \
   ! systemctl is-active --quiet mysql  2>/dev/null; then
    echo "[setup-test-db] Starting MariaDB/MySQL …"
    systemctl start mariadb 2>/dev/null || systemctl start mysql
fi

# ---------- create database & user ------------------------------------------
echo "[setup-test-db] Creating database and user …"

SQL_STATEMENTS="
CREATE DATABASE IF NOT EXISTS \`bga_test\`
    CHARACTER SET utf8
    COLLATE utf8_general_ci;

CREATE USER IF NOT EXISTS 'bga_test'@'127.0.0.1' IDENTIFIED BY 'bga_test';
CREATE USER IF NOT EXISTS 'bga_test'@'localhost'  IDENTIFIED BY 'bga_test';

GRANT ALL PRIVILEGES ON \`bga_test\`.* TO 'bga_test'@'127.0.0.1';
GRANT ALL PRIVILEGES ON \`bga_test\`.* TO 'bga_test'@'localhost';

FLUSH PRIVILEGES;
"

if command -v mariadb &>/dev/null; then
    echo "$SQL_STATEMENTS" | mariadb --batch
else
    echo "$SQL_STATEMENTS" | mysql --batch
fi

echo "[setup-test-db] Done. DB bga_test is ready (user: bga_test / pass: bga_test)."
