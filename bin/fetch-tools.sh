#!/usr/bin/env bash
# Télécharge les outils de développement dans tools/ (ignoré par Git).
# Sources : GitHub uniquement, pour fonctionner derrière un pare-feu qui bloque wordpress.org et packagist.
set -euo pipefail
cd "$(dirname "$0")/.."

WP_CLI_VERSION="${WP_CLI_VERSION:-2.12.0}"
PHPCS_VERSION="${PHPCS_VERSION:-3.13.5}"
WPCS_REF="${WPCS_REF:-3.4.1}"
PHPCSUTILS_REF="${PHPCSUTILS_REF:-1.2.3}"
PHPCSEXTRA_REF="${PHPCSEXTRA_REF:-1.5.1}"

mkdir -p tools/standards
fetch() { # url destination
  [ -s "$2" ] || curl -fsSL --retry 3 -o "$2" "$1"
}
fetch "https://github.com/wp-cli/wp-cli/releases/download/v${WP_CLI_VERSION}/wp-cli-${WP_CLI_VERSION}.phar" tools/wp-cli.phar
fetch "https://github.com/PHPCSStandards/PHP_CodeSniffer/releases/download/${PHPCS_VERSION}/phpcs.phar" tools/phpcs.phar
fetch "https://github.com/PHPCSStandards/PHP_CodeSniffer/releases/download/${PHPCS_VERSION}/phpcbf.phar" tools/phpcbf.phar

clone() { # repo ref dir
  [ -d "$3/.git" ] || git -c advice.detachedHead=false clone -q --depth 1 --branch "$2" "https://github.com/$1.git" "$3"
}
clone WordPress/WordPress-Coding-Standards "$WPCS_REF" tools/standards/wpcs
clone PHPCSStandards/PHPCSUtils "$PHPCSUTILS_REF" tools/standards/phpcsutils
clone PHPCSStandards/PHPCSExtra "$PHPCSEXTRA_REF" tools/standards/phpcsextra

echo "Outils prêts dans tools/"
