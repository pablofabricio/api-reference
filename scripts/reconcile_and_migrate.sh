#!/usr/bin/env bash
set -euo pipefail

PHP_CONTAINER="api-reference-php"
DB_CONTAINER="api-reference-db"
DB_NAME="api_reference"
DB_USER="api-user"

require_running_container() {
  local name="$1"
  if ! docker ps --format '{{.Names}}' | grep -qx "$name"; then
    echo "Container $name nao esta em execucao."
    echo "Suba os containers com: docker compose up -d --build"
    exit 1
  fi
}

echo "Verificando containers..."
require_running_container "$PHP_CONTAINER"
require_running_container "$DB_CONTAINER"

echo "Garantindo tabela de migrations..."
docker exec -i "$DB_CONTAINER" psql -U "$DB_USER" -d "$DB_NAME" <<'SQL'
CREATE TABLE IF NOT EXISTS migrations (
  id SERIAL PRIMARY KEY,
  migration VARCHAR(255) NOT NULL,
  batch INTEGER NOT NULL
);
SQL

echo "Reconciliando schema base materializado fora de migrations..."
docker exec -i "$DB_CONTAINER" psql -U "$DB_USER" -d "$DB_NAME" <<'SQL'
ALTER TABLE users ADD COLUMN IF NOT EXISTS email_verified_at TIMESTAMP NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS remember_token VARCHAR(100) NULL;
CREATE UNIQUE INDEX IF NOT EXISTS users_email_unique ON users(email);

CREATE TABLE IF NOT EXISTS password_reset_tokens (
  email VARCHAR(255) PRIMARY KEY,
  token VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NULL
);

CREATE TABLE IF NOT EXISTS sessions (
  id VARCHAR(255) PRIMARY KEY,
  user_id BIGINT NULL,
  ip_address VARCHAR(45) NULL,
  user_agent TEXT NULL,
  payload TEXT NOT NULL,
  last_activity INTEGER NOT NULL
);

CREATE INDEX IF NOT EXISTS sessions_user_id_index ON sessions(user_id);
CREATE INDEX IF NOT EXISTS sessions_last_activity_index ON sessions(last_activity);
SQL

echo "Registrando migrations ja materializadas..."
docker exec -i "$DB_CONTAINER" psql -U "$DB_USER" -d "$DB_NAME" <<'SQL'
INSERT INTO migrations (migration, batch)
SELECT '0001_01_01_000000_create_users_table', 1
WHERE NOT EXISTS (
  SELECT 1 FROM migrations WHERE migration = '0001_01_01_000000_create_users_table'
);

INSERT INTO migrations (migration, batch)
SELECT '2026_03_25_120000_add_description_to_users_table', 1
WHERE NOT EXISTS (
  SELECT 1 FROM migrations WHERE migration = '2026_03_25_120000_add_description_to_users_table'
);

INSERT INTO migrations (migration, batch)
SELECT '2026_03_25_180000_create_channel_join_requests_table', 1
WHERE NOT EXISTS (
  SELECT 1 FROM migrations WHERE migration = '2026_03_25_180000_create_channel_join_requests_table'
);
SQL

echo "Aplicando migrations pendentes..."
docker exec "$PHP_CONTAINER" php artisan migrate --force

echo "Status final das migrations:"
docker exec "$PHP_CONTAINER" php artisan migrate:status

echo "Concluido."
