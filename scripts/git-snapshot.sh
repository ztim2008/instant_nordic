#!/usr/bin/env bash
set -euo pipefail

message="${1:-snapshot: $(date +%F\ %T)}"
tag="snapshot/$(date +%Y%m%d-%H%M%S)"

git rev-parse --is-inside-work-tree >/dev/null

git add -A

if git diff --cached --quiet; then
    echo "No staged changes to commit. Reusing current HEAD for tag."
else
    git commit -m "$message"
fi

git tag -a "$tag" -m "$message"
echo "$tag"
