#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
if command -v docker >/dev/null 2>&1; then
  docker compose -f docker-compose.websocket.yml up --build
else
  echo "Docker is not available in this environment; install Docker or run a Pusher-compatible server elsewhere." >&2
  exit 1
fi
