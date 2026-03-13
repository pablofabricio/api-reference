#!/usr/bin/env bash
set -euo pipefail

CONTAINER_NAME="api-reference-db"
DB_NAME="api_reference"
DB_USER="api-user"
DDL_FILE="ddl.sql"
SCHEMA_OUT="scripts/schema_from_db.sql"

if ! docker ps --format '{{.Names}}' | grep -qx "$CONTAINER_NAME"; then
  echo "Container $CONTAINER_NAME nao esta em execucao."
  echo "Suba o banco com: docker compose up -d db"
  exit 1
fi

if [[ ! -f "$DDL_FILE" ]]; then
  echo "Arquivo $DDL_FILE nao encontrado na raiz do projeto."
  exit 1
fi

echo "Aplicando $DDL_FILE em $DB_NAME..."
docker exec -i "$CONTAINER_NAME" psql -U "$DB_USER" -d "$DB_NAME" < "$DDL_FILE"

echo "Exportando schema para $SCHEMA_OUT..."
docker exec -i "$CONTAINER_NAME" pg_dump -U "$DB_USER" -d "$DB_NAME" --schema-only --no-owner --no-privileges > "$SCHEMA_OUT"

echo "Concluido."
