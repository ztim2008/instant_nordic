#!/usr/bin/env bash
set -euo pipefail

ref="${1:-}"

if [[ -z "$ref" ]]; then
    echo "Usage: $0 <tag-or-commit> [branch-name]"
    exit 1
fi

safe_ref="${ref//\//-}"
branch="${2:-restore/$safe_ref}"

git rev-parse --verify "$ref" >/dev/null
git switch -c "$branch" "$ref"
echo "Created branch: $branch"
