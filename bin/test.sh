#!/usr/bin/env bash
# Lance les contrôles et les tests (le site local doit tourner : bin/serve.sh).
set -euo pipefail
cd "$(dirname "$0")/.."
export WP_CLI_ALLOW_ROOT=1
bin/lint.sh
echo "→ Tests des coordonnées"
php tools/wp-cli.phar --path=.local/wordpress eval-file tests/coordonnees.php
echo "→ Tests du formulaire de devis"
php tools/wp-cli.phar --path=.local/wordpress eval-file tests/devis.php
echo "→ Tests du référencement, de la sécurité et des images"
php tools/wp-cli.phar --path=.local/wordpress eval-file tests/seo-securite.php
echo "→ Sécurité dans le navigateur (CSP, images)"
python3 tests/navigateur.py
echo "→ Validité des blocs dans l'éditeur"
python3 tests/editor_blocks.py
