#!/usr/bin/env bash
# Local dev server with SQLite:  bash dev/run.sh   → http://localhost:8080
set -e
cd "$(dirname "$0")/.."
[ -f config.local.php ] || cp dev/config.local.dev.php config.local.php
if [ ! -f dev/wsap.sqlite ]; then
  php -c dev/php.ini -r '$p=new PDO("sqlite:dev/wsap.sqlite");$p->exec(file_get_contents("db/schema.sqlite.sql"));echo "sqlite schema applied\n";'
fi
exec php -c dev/php.ini -S localhost:8080 dev/router.php
