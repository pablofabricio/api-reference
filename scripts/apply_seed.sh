#!/usr/bin/env bash
set -euo pipefail

CONTAINER_NAME="api-reference-db"
DB_NAME="api_reference"
DB_USER="api-user"
SEED_FILE="scripts/seed_data.sql"

if ! docker ps --format '{{.Names}}' | grep -qx "$CONTAINER_NAME"; then
  echo "Container $CONTAINER_NAME nao esta em execucao."
  echo "Suba o banco com: docker compose up -d db"
  exit 1
fi

if [[ ! -f "$SEED_FILE" ]]; then
  echo "Arquivo $SEED_FILE nao encontrado."
  exit 1
fi

echo "Aplicando seed em $DB_NAME..."
docker exec -i "$CONTAINER_NAME" psql -U "$DB_USER" -d "$DB_NAME" < "$SEED_FILE"

echo "Concluido."
