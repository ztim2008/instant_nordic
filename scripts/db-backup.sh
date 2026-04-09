#!/usr/bin/env bash
set -euo pipefail

root_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
config_file="$root_dir/system/config/config.php"
backup_dir="$root_dir/backups/db"

die() {
    echo "[db-backup] $*" >&2
    exit 1
}

if [[ ! -f "$config_file" ]]; then
    die "Config file not found: $config_file"
fi

mkdir -p "$backup_dir"

readarray -t db_data < <(php -r '$cfg = include $argv[1]; echo $cfg["db_host"], PHP_EOL, $cfg["db_base"], PHP_EOL, $cfg["db_user"], PHP_EOL, $cfg["db_pass"], PHP_EOL;' "$config_file")

cfg_db_host="${db_data[0]:-}"
cfg_db_base="${db_data[1]:-}"
cfg_db_user="${db_data[2]:-}"
cfg_db_pass="${db_data[3]:-}"

db_host="${DB_DUMP_HOST:-$cfg_db_host}"
db_base="${DB_DUMP_BASE:-$cfg_db_base}"
db_user="${DB_DUMP_USER:-$cfg_db_user}"
db_pass="${DB_DUMP_PASS:-$cfg_db_pass}"

if [[ -z "$db_host" || -z "$db_base" || -z "$db_user" ]]; then
    die "Empty DB connection params (host/base/user). Check system config or DB_DUMP_* env overrides."
fi

if ! command -v mysqldump >/dev/null 2>&1; then
    die "mysqldump command is not available in PATH"
fi

timestamp="$(date +%Y%m%d-%H%M%S)"
outfile="$backup_dir/${db_base}-${timestamp}.sql.gz"
tmpfile="$outfile.tmp"

if command -v mysql >/dev/null 2>&1; then
    preflight_ok=0
    if MYSQL_PWD="$db_pass" mysql -h "$db_host" -u "$db_user" -D "$db_base" -Nse 'SELECT 1' >/dev/null 2>&1; then
        preflight_ok=1
    elif MYSQL_PWD="$db_pass" mysql --protocol=TCP -h "$db_host" -u "$db_user" -D "$db_base" -Nse 'SELECT 1' >/dev/null 2>&1; then
        preflight_ok=1
    fi

    if [[ "$preflight_ok" -ne 1 ]]; then
        die "DB auth preflight failed for user '$db_user' on host '$db_host' and base '$db_base'. Set DB_DUMP_USER/DB_DUMP_PASS or fix MySQL grants."
    fi
fi

rm -f "$tmpfile"
if ! MYSQL_PWD="$db_pass" mysqldump -h "$db_host" -u "$db_user" --single-transaction --routines --triggers "$db_base" | gzip > "$tmpfile"; then
    rm -f "$tmpfile"
    die "mysqldump failed (host='$db_host', base='$db_base', user='$db_user')."
fi

mv "$tmpfile" "$outfile"

echo "$outfile"
