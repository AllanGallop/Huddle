#!/usr/bin/env sh
set -e

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
HUDDLE="$ROOT/huddle"

echo "Resetting install artifacts on host..."
rm -f "$HUDDLE/.env"
rm -f "$HUDDLE/database/database.sqlite"
cp "$HUDDLE/public/.htaccess.setup" "$HUDDLE/public/.htaccess"

cd "$ROOT"
docker compose -f DockerCompose.yaml down -v
echo ""
echo "Open http://localhost:8000 — MySQL host is db"
echo ""
docker compose -f DockerCompose.yaml up --build
