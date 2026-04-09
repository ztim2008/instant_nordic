#!/usr/bin/env bash
set -euo pipefail

root_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
config_file="$root_dir/system/config/config.php"
backup_dir="$root_dir/backups/db"
dump_env_default="$backup_dir/.db-dump.env"
dump_env_file="${DB_DUMP_ENV_FILE:-$dump_env_default}"

die() {
    echo "[db-backup] $*" >&2
    exit 1
}

if [[ ! -f "$config_file" ]]; then
    die "Config file not found: $config_file"
fi

mkdir -p "$backup_dir"

# Optional local dump credentials file (ignored by git):
# backups/db/.db-dump.env
#
# Format:
# DB_DUMP_HOST=localhost
# DB_DUMP_BASE=builders
# DB_DUMP_USER=lb_dump
# DB_DUMP_PASS=secret
env_db_dump_host='__UNSET__'
env_db_dump_base='__UNSET__'
env_db_dump_user='__UNSET__'
env_db_dump_pass='__UNSET__'

if [[ -n "${DB_DUMP_HOST+x}" ]]; then env_db_dump_host="$DB_DUMP_HOST"; fi
if [[ -n "${DB_DUMP_BASE+x}" ]]; then env_db_dump_base="$DB_DUMP_BASE"; fi
if [[ -n "${DB_DUMP_USER+x}" ]]; then env_db_dump_user="$DB_DUMP_USER"; fi
if [[ -n "${DB_DUMP_PASS+x}" ]]; then env_db_dump_pass="$DB_DUMP_PASS"; fi

if [[ -f "$dump_env_file" ]]; then
    # shellcheck disable=SC1090
    source "$dump_env_file"
fi

if [[ "$env_db_dump_host" != '__UNSET__' ]]; then DB_DUMP_HOST="$env_db_dump_host"; fi
if [[ "$env_db_dump_base" != '__UNSET__' ]]; then DB_DUMP_BASE="$env_db_dump_base"; fi
if [[ "$env_db_dump_user" != '__UNSET__' ]]; then DB_DUMP_USER="$env_db_dump_user"; fi
if [[ "$env_db_dump_pass" != '__UNSET__' ]]; then DB_DUMP_PASS="$env_db_dump_pass"; fi

readarray -t db_data < <(php -r '$cfg = include $argv[1]; echo $cfg["db_host"], PHP_EOL, $cfg["db_base"], PHP_EOL, $cfg["db_user"], PHP_EOL, $cfg["db_pass"], PHP_EOL;' "$config_file")

cfg_db_host="${db_data[0]:-}"
cfg_db_base="${db_data[1]:-}"
cfg_db_user="${db_data[2]:-}"
cfg_db_pass="${db_data[3]:-}"

db_host="${DB_DUMP_HOST:-$cfg_db_host}"
db_base="${DB_DUMP_BASE:-$cfg_db_base}"
db_user="${DB_DUMP_USER:-$cfg_db_user}"
# For pass we allow explicit empty value (e.g. auth_socket users).
db_pass="${DB_DUMP_PASS-$cfg_db_pass}"

if [[ -z "$db_host" || -z "$db_base" || -z "$db_user" ]]; then
    die "Empty DB connection params (host/base/user). Check system config or DB_DUMP_* env overrides."
fi

if ! command -v mysqldump >/dev/null 2>&1; then
    die "mysqldump command is not available in PATH"
fi

mysql_auth_args=()
if [[ -n "$db_pass" ]]; then
    mysql_auth_args+=("--password=$db_pass")
fi

timestamp="$(date +%Y%m%d-%H%M%S)"
outfile="$backup_dir/${db_base}-${timestamp}.sql.gz"
tmpfile="$outfile.tmp"

if command -v mysql >/dev/null 2>&1; then
    preflight_ok=0
    if mysql -h "$db_host" -u "$db_user" "${mysql_auth_args[@]}" -D "$db_base" -Nse 'SELECT 1' >/dev/null 2>&1; then
        preflight_ok=1
    elif mysql --protocol=TCP -h "$db_host" -u "$db_user" "${mysql_auth_args[@]}" -D "$db_base" -Nse 'SELECT 1' >/dev/null 2>&1; then
        preflight_ok=1
    fi

    if [[ "$preflight_ok" -ne 1 ]]; then
        die "DB auth preflight failed for user '$db_user' on host '$db_host' and base '$db_base'. Set DB_DUMP_USER/DB_DUMP_PASS or fix MySQL grants."
    fi
fi

rm -f "$tmpfile"
if ! mysqldump -h "$db_host" -u "$db_user" "${mysql_auth_args[@]}" --single-transaction --routines --triggers --no-tablespaces "$db_base" | gzip > "$tmpfile"; then
    rm -f "$tmpfile"
    die "mysqldump failed (host='$db_host', base='$db_base', user='$db_user')."
fi

mv "$tmpfile" "$outfile"

echo "$outfile"
