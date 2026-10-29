#!/usr/bin/env bash
# Portable database export — replaces MariaDB 11 uca1400 collation so the dump
# can be imported on MySQL or older MariaDB without "#1273 Unknown collation".
#
# Usage: ./scripts/db-export.sh [output-file]
# Reads DB_* variables from .env in the project root (or falls back to defaults).

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ENV_FILE="$SCRIPT_DIR/../.env"

# Load .env values if present
if [[ -f "$ENV_FILE" ]]; then
    # export only the DB_ lines, ignore comments and blank lines
    set -o allexport
    # shellcheck disable=SC1090
    source <(grep -E '^DB_' "$ENV_FILE" | sed 's/\r//')
    set +o allexport
fi

DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"
DB_DATABASE="${DB_DATABASE:-laravel}"
DB_USERNAME="${DB_USERNAME:-root}"
DB_PASSWORD="${DB_PASSWORD:-}"
OUTPUT="${1:-${DB_DATABASE}_$(date +%Y%m%d_%H%M%S).sql}"

MYSQL_PWD="$DB_PASSWORD" mysqldump \
    --host="$DB_HOST" \
    --port="$DB_PORT" \
    --user="$DB_USERNAME" \
    --single-transaction \
    --routines \
    --triggers \
    "$DB_DATABASE" \
  | sed 's/utf8mb4_uca1400_ai_ci/utf8mb4_unicode_ci/g' \
  > "$OUTPUT"

echo "Exported: $OUTPUT"
