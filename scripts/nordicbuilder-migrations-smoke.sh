#!/usr/bin/env bash
set -euo pipefail

root_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
package_dir="$root_dir/packages/nordicbuilder"

install_php="$package_dir/install.php"
install_sql="$package_dir/install.sql"
migrations_dir="$package_dir/migrations"
php_smoke_runner="$root_dir/scripts/nordicbuilder-migrations-smoke.php"

run_db=0

usage() {
    echo "Usage: bash scripts/nordicbuilder-migrations-smoke.sh [--db]"
    echo ""
    echo "Modes:"
    echo "  default: static checks only (safe, no DB writes)"
    echo "  --db: run install/update smoke against DB with temporary table prefix"
}

if [[ "${1:-}" == "-h" || "${1:-}" == "--help" ]]; then
    usage
    exit 0
fi

if [[ "${1:-}" == "--db" ]]; then
    run_db=1
fi

if [[ ! -f "$install_php" || ! -f "$install_sql" || ! -d "$migrations_dir" ]]; then
    echo "Required nordicbuilder package files are missing" >&2
    exit 1
fi

mapfile -t migration_files < <(find "$migrations_dir" -maxdepth 1 -type f -name '*.sql' | sort)
if [[ ${#migration_files[@]} -eq 0 ]]; then
    echo "No migration files found in $migrations_dir" >&2
    exit 1
fi

php -l "$install_php" >/dev/null

prev=""
for file in "${migration_files[@]}"; do
    base="$(basename "$file")"
    if [[ ! "$base" =~ ^[0-9]{3}_[a-zA-Z0-9_]+\.sql$ ]]; then
        echo "Invalid migration filename format: $base" >&2
        exit 1
    fi
    if [[ -n "$prev" && "$base" < "$prev" ]]; then
        echo "Migrations are not sorted: $prev then $base" >&2
        exit 1
    fi
    if ! grep -Eiq 'CREATE[[:space:]]+TABLE|ALTER[[:space:]]+TABLE|CREATE[[:space:]]+INDEX|DROP[[:space:]]+INDEX' "$file"; then
        echo "Migration has no schema operation: $base" >&2
        exit 1
    fi
    prev="$base"
done

if ! grep -q "nordicbuilder_migrations" "$install_sql"; then
    echo "install.sql must contain nordicbuilder_migrations table" >&2
    exit 1
fi

echo "Static migration checks: OK"

if [[ $run_db -eq 0 ]]; then
    echo "DB smoke not requested. Run with --db for install/update cycle."
    exit 0
fi

php_bin="/opt/php84/bin/php"
if [[ ! -x "$php_bin" ]]; then
    php_bin="php"
fi

if [[ ! -f "$php_smoke_runner" ]]; then
    echo "PHP smoke runner not found: $php_smoke_runner" >&2
    exit 1
fi

"$php_bin" "$php_smoke_runner"
