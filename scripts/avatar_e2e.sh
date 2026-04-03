#!/usr/bin/env bash
set -euo pipefail

BASE_URL="${BASE_URL:-http://localhost:8000/api}"
EMAIL="${EMAIL:-}"
PASSWORD="${PASSWORD:-}"
FILE="${FILE:-}"
CONTENT_TYPE="${CONTENT_TYPE:-image/png}"

if [[ -z "${EMAIL}" || -z "${PASSWORD}" || -z "${FILE}" ]]; then
  echo "Usage: EMAIL=... PASSWORD=... FILE=/path/avatar.png [BASE_URL=http://localhost:8000/api] [CONTENT_TYPE=image/png] $0"
  exit 1
fi

if ! command -v jq >/dev/null 2>&1; then
  echo "Error: jq nao encontrado. Instale jq para continuar."
  exit 1
fi

if [[ ! -f "${FILE}" ]]; then
  echo "Error: arquivo nao encontrado: ${FILE}"
  exit 1
fi

echo "1) Fazendo login em ${BASE_URL}/auth/login"
LOGIN_JSON=$(curl -sS -X POST "${BASE_URL}/auth/login" \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"${EMAIL}\",\"password\":\"${PASSWORD}\"}")

TOKEN=$(echo "${LOGIN_JSON}" | jq -r '.access_token // empty')
if [[ -z "${TOKEN}" ]]; then
  echo "Falha no login. Resposta:"
  echo "${LOGIN_JSON}" | jq . || echo "${LOGIN_JSON}"
  exit 1
fi

echo "2) Descobrindo usuario logado"
ME_JSON=$(curl -sS "${BASE_URL}/me" -H "Authorization: Bearer ${TOKEN}")
USER_ID=$(echo "${ME_JSON}" | jq -r '.id // empty')
if [[ -z "${USER_ID}" ]]; then
  echo "Falha ao obter usuario logado. Resposta:"
  echo "${ME_JSON}" | jq . || echo "${ME_JSON}"
  exit 1
fi

echo "3) Solicitando upload URL assinada"
UPLOAD_REQ=$(curl -sS -X POST "${BASE_URL}/users/${USER_ID}/avatar/upload-url" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Content-Type: application/json" \
  -d "{\"file_name\":\"$(basename "${FILE}")\",\"content_type\":\"${CONTENT_TYPE}\"}")

KEY=$(echo "${UPLOAD_REQ}" | jq -r '.key // empty')
UPLOAD_URL=$(echo "${UPLOAD_REQ}" | jq -r '.upload_url // empty')

if [[ -z "${KEY}" || -z "${UPLOAD_URL}" ]]; then
  echo "Falha ao obter URL assinada. Resposta:"
  echo "${UPLOAD_REQ}" | jq . || echo "${UPLOAD_REQ}"
  exit 1
fi

echo "4) Enviando arquivo para storage"
# Captura headers assinados e envia no PUT
HEADER_ARGS=()
while IFS= read -r header_line; do
  HEADER_ARGS+=( -H "${header_line}" )
done < <(echo "${UPLOAD_REQ}" | jq -r '.headers // {} | to_entries[] | "\(.key): \(.value)"')

curl -sS -X PUT "${UPLOAD_URL}" \
  "${HEADER_ARGS[@]}" \
  -H "Content-Type: ${CONTENT_TYPE}" \
  --upload-file "${FILE}" >/dev/null

echo "5) Confirmando avatar no backend"
AVATAR_JSON=$(curl -sS -X PUT "${BASE_URL}/users/${USER_ID}/avatar" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Content-Type: application/json" \
  -d "{\"key\":\"${KEY}\"}")

echo "Resposta update avatar:"
echo "${AVATAR_JSON}" | jq . || echo "${AVATAR_JSON}"

echo "6) Lendo perfil"
PROFILE_JSON=$(curl -sS "${BASE_URL}/users/${USER_ID}/profile" \
  -H "Authorization: Bearer ${TOKEN}")

echo "Avatar final no perfil:"
echo "${PROFILE_JSON}" | jq '.data.user.avatar_url // .user.avatar_url // null'

echo "OK: fluxo de avatar finalizado."
