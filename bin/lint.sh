#!/usr/bin/env bash
# Contrôles de code : normes WordPress (PHPCS), syntaxe PHP, validité des fichiers JSON.
set -euo pipefail
cd "$(dirname "$0")/.."
[ -f tools/phpcs.phar ] || bin/fetch-tools.sh
# Sous Git Bash (Windows), PHP attend un chemin « C:/… » : pwd -W le fournit.
STD="$(pwd -W 2>/dev/null || pwd)/tools/standards"
echo "→ Syntaxe PHP"
find wp-content bin -name '*.php' -print0 | xargs -0 -n1 php -l -d display_errors=1 | grep -v '^No syntax errors' || true
echo "→ JSON"
for f in $(find wp-content -name '*.json'); do php -r 'json_decode(file_get_contents($argv[1]), false, 512, JSON_THROW_ON_ERROR);' "$f" && echo "  ok $f"; done
echo "→ Syntaxe JavaScript"
find wp-content -name "*.js" -not -path "*/node_modules/*" -print0 | xargs -0 -r -n1 node --check
echo "→ PHPCS (WordPress)"
php tools/phpcs.phar --runtime-set installed_paths "$STD/wpcs,$STD/phpcsutils,$STD/phpcsextra" "$@"
