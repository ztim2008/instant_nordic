#!/usr/bin/env bash
set -euo pipefail

root_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
message="${1:-checkpoint: $(date +%F\ %T)}"
db_backup_mode="${CHECKPOINT_DB_BACKUP_MODE:-required}"

cd "$root_dir"

db_backup="$root_dir/scripts/db-backup.sh"

if [[ -x "$db_backup" || -f "$db_backup" ]]; then
    case "$db_backup_mode" in
        skip)
            echo "[checkpoint] DB backup skipped (CHECKPOINT_DB_BACKUP_MODE=skip)"
            ;;
        best-effort)
            if ! "$db_backup"; then
                echo "[checkpoint] WARNING: DB backup failed, continuing due to CHECKPOINT_DB_BACKUP_MODE=best-effort" >&2
            fi
            ;;
        required)
            "$db_backup"
            ;;
        *)
            echo "[checkpoint] Unknown CHECKPOINT_DB_BACKUP_MODE='$db_backup_mode'. Allowed: required|best-effort|skip" >&2
            exit 1
            ;;
    esac
fi

"$root_dir/scripts/git-snapshot.sh" "$message"
