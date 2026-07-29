# Local Setup

The build exercises need `buyvia` running on your machine with **MySQL 8** and **Redis**.
MySQL specifically — not SQLite. Row-level locking (`SELECT ... FOR UPDATE`), which is
central to Phase 2, is InnoDB behaviour that SQLite cannot demonstrate.

## Option A — Laravel Sail (recommended)

`laravel/sail` is already in `require-dev`, so this is nearly free. It gives you MySQL 8
and Redis in containers without installing either natively. Requires Docker Desktop.

```bash
composer install

cp .env.example .env
php artisan key:generate

# Add Sail's MySQL + Redis services
php artisan sail:install --with=mysql,redis

./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
```

Sail rewrites the relevant `.env` values for you (`DB_HOST=mysql`, `REDIS_HOST=redis`).

Useful aliases:

```bash
alias sail='./vendor/bin/sail'
sail artisan tinker
sail mysql            # mysql shell
sail redis            # redis-cli
```

## Option B — Native install

If you'd rather not use Docker:

**Requirements**
- PHP 8.2+ with `pdo_mysql`, `redis`, `mbstring`, `bcmath`, `intl`
- MySQL 8.0+
- Redis 6+

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Then edit `.env`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=buyvia
DB_USERNAME=buyvia
DB_PASSWORD=<yours>

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
```

Note the last three: `.env.example` currently defaults cache, queue and session to
`database`. Switch them to `redis` — several exercises depend on it.

Create the database and user:

```sql
CREATE DATABASE buyvia CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'buyvia'@'127.0.0.1' IDENTIFIED BY '<yours>';
GRANT ALL PRIVILEGES ON buyvia.* TO 'buyvia'@'127.0.0.1';
FLUSH PRIVILEGES;
```

Then:

```bash
php artisan migrate
php artisan serve
```

## Verifying it works

```bash
php artisan migrate:status      # 23 migrations, all Ran
php artisan tinker
>>> \App\Models\Product::count();        // 0, but no error = DB fine
>>> Cache::put('x', 1); Cache::get('x'); // 1 = Redis fine
```

If both succeed, you're ready.

## A note on MariaDB

MariaDB mostly works — this schema uses `utf8mb4_unicode_ci` rather than MySQL 8's
`utf8mb4_0900_ai_ci`, and MariaDB 10.11 supports the stored generated columns and CHECK
constraints in these migrations. But prefer real MySQL 8 if you have the choice: some
locking, JSON and optimiser behaviour differs, and you don't want to learn a behaviour
in an interview that turns out to be MariaDB-specific.

## Troubleshooting

**`SQLSTATE[HY000] [2002] Connection refused`** — MySQL isn't running, or `DB_HOST` is
wrong. Under Sail it must be `mysql`, not `127.0.0.1`.

**`Class "Redis" not found`** — the phpredis extension is missing. Either install it, or
set `REDIS_CLIENT=predis` and `composer require predis/predis`.

**Migrations fail on generated columns** — you're on MySQL 5.7 or an old MariaDB.
`product_variants` uses `storedAs(...)` with `JSON_EXTRACT`, which needs MySQL 5.7.8+ and
realistically 8.0.

**Stale config after editing `.env`** — `php artisan config:clear`.
