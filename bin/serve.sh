#!/usr/bin/env bash
# Sert le site local sur http://127.0.0.1:8080 (serveur PHP intégré, via WP-CLI).
set -euo pipefail
cd "$(dirname "$0")/.."
export WP_CLI_ALLOW_ROOT=1
exec php tools/wp-cli.phar --path=.local/wordpress server --host=127.0.0.1 --port="${PORT:-8080}"
