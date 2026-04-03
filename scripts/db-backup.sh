#!/usr/bin/env bash
set -euo pipefail

root_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
config_file="$root_dir/system/config/config.php"
backup_dir="$root_dir/backups/db"

if [[ ! -f "$config_file" ]]; then
    echo "Config file not found: $config_file"
    exit 1
fi

mkdir -p "$backup_dir"

readarray -t db_data < <(php -r '$cfg = include $argv[1]; echo $cfg["db_host"], PHP_EOL, $cfg["db_base"], PHP_EOL, $cfg["db_user"], PHP_EOL, $cfg["db_pass"], PHP_EOL;' "$config_file")

db_host="${db_data[0]}"
db_base="${db_data[1]}"
db_user="${db_data[2]}"
db_pass="${db_data[3]}"

timestamp="$(date +%Y%m%d-%H%M%S)"
outfile="$backup_dir/${db_base}-${timestamp}.sql.gz"

MYSQL_PWD="$db_pass" mysqldump -h "$db_host" -u "$db_user" --single-transaction --routines --triggers "$db_base" | gzip > "$outfile"

echo "$outfile"
