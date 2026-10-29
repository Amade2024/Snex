#!/usr/bin/env bash
# Installe un WordPress local reproductible pour développer le site S-NEX.
# - WordPress depuis le miroir officiel GitHub, base SQLite (aucun serveur MySQL ni Docker requis)
# - thème « snex » et extension « snex-core » reliés par liens symboliques
# - contenus de départ (pages, menu, logo, médias) via bin/seed.php
# Relancer le script est sans danger : chaque étape vérifie ce qui existe déjà.
set -euo pipefail
cd "$(dirname "$0")/.."
ROOT="$PWD"

WP_VERSION="${WP_VERSION:-7.1.3}"
SQLITE_REF="${SQLITE_REF:-v3.1.0}"
SITE_URL="${SITE_URL:-http://127.0.0.1:8080}"
WP_DIR="$ROOT/.local/wordpress"
export WP_CLI_ALLOW_ROOT=1

[ -f tools/wp-cli.phar ] || bin/fetch-tools.sh
wp() { php "$ROOT/tools/wp-cli.phar" --path="$WP_DIR" "$@"; }

# 1. Cœur WordPress
if [ ! -f "$WP_DIR/wp-load.php" ]; then
  echo "→ WordPress $WP_VERSION"
  git -c advice.detachedHead=false clone -q --depth 1 --branch "$WP_VERSION" https://github.com/WordPress/WordPress.git "$WP_DIR"
  rm -rf "$WP_DIR/.git"
fi

# 2. Base SQLite (développement local uniquement ; la production utilise MySQL ou MariaDB)
SQ="$WP_DIR/wp-content/plugins/sqlite-database-integration"
if [ ! -d "$SQ" ]; then
  echo "→ Intégration SQLite $SQLITE_REF"
  TMP="$(mktemp -d)"
  git -c advice.detachedHead=false clone -q --depth 1 --branch "$SQLITE_REF" https://github.com/WordPress/sqlite-database-integration.git "$TMP/repo"
  # Le dépôt est un monorepo : l'extension pointe vers le moteur par lien symbolique, copié ici en clair.
  cp -rL "$TMP/repo/packages/plugin-sqlite-database-integration" "$SQ"
  rm -rf "$TMP"
fi
sed -e "s#{SQLITE_IMPLEMENTATION_FOLDER_PATH}#$SQ#" -e "s#{SQLITE_PLUGIN}#sqlite-database-integration/load.php#" \
  "$SQ/db.copy" > "$WP_DIR/wp-content/db.php"

# 3. Code du projet
ln -sfn "$ROOT/wp-content/themes/snex" "$WP_DIR/wp-content/themes/snex"
ln -sfn "$ROOT/wp-content/plugins/snex-core" "$WP_DIR/wp-content/plugins/snex-core"
# E-mails écrits dans .local/wordpress/wp-content/snex-mails au lieu d'être envoyés (local uniquement).
mkdir -p "$WP_DIR/wp-content/mu-plugins"
cp "$ROOT/bin/mail-local.php" "$WP_DIR/wp-content/mu-plugins/snex-mail-local.php"

# 4. Configuration et installation
if [ ! -f "$WP_DIR/wp-config.php" ]; then
  wp config create --dbname=snex --dbuser=local --dbpass=local --dbhost=localhost --skip-check --quiet
  wp config set WP_ENVIRONMENT_TYPE local --quiet
  wp config set WP_DEBUG true --raw --quiet
  wp config set WP_DEBUG_LOG true --raw --quiet
  wp config set WP_DEBUG_DISPLAY false --raw --quiet
  wp config set DISALLOW_FILE_EDIT true --raw --quiet
fi
if ! wp core is-installed 2>/dev/null; then
  PASS="$(php -r 'echo bin2hex(random_bytes(12));')"
  wp core install --url="$SITE_URL" --title="S-NEX SARL" --admin_user=admin \
    --admin_password="$PASS" --admin_email=admin@snex.local --skip-email --quiet
  umask 077; printf 'Utilisateur : admin\nMot de passe : %s\n' "$PASS" > "$ROOT/.local/admin.txt"
  echo "→ Accès administrateur enregistrés dans .local/admin.txt (fichier ignoré par Git)"
fi

wp plugin activate sqlite-database-integration snex-core --quiet
wp theme activate snex --quiet
wp rewrite structure '/%postname%/' --quiet
wp option update timezone_string 'Africa/Abidjan' --quiet
wp option update date_format 'j F Y' --quiet
wp option update default_comment_status closed --quiet
wp option update default_ping_status closed --quiet
wp eval-file "$ROOT/bin/seed.php"
wp rewrite flush --quiet

echo "Site prêt. Lancez : bin/serve.sh  puis ouvrez $SITE_URL"
