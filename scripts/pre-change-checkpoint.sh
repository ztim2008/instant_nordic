#!/usr/bin/env bash
set -euo pipefail

root_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
message="${1:-checkpoint: $(date +%F\ %T)}"

cd "$root_dir"

db_backup="$root_dir/scripts/db-backup.sh"

if [[ -x "$db_backup" || -f "$db_backup" ]]; then
    "$db_backup"
fi

"$root_dir/scripts/git-snapshot.sh" "$message"
