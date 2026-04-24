#!/usr/bin/env bash
set -euo pipefail

root_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
version="${1:-}"

if [[ -n "$version" ]]; then
    bash "$root_dir/scripts/nordicblocks-version-sync.sh" "$version"
fi

bash "$root_dir/scripts/build-nordicblocks-package.sh"

if [[ -n "$version" ]]; then
    bash "$root_dir/scripts/build-nordicblocks-update-package.sh" "$version"
else
    bash "$root_dir/scripts/build-nordicblocks-update-package.sh"
fi

bash "$root_dir/scripts/nordicblocks-sync-public-staging.sh"

current_version="$(tr -d '[:space:]' < "$root_dir/packages/nordicblocks/VERSION")"

echo "NordicBlocks public release contour completed"
echo " - version: $current_version"
echo " - install: $root_dir/dist/nordicblocks/start/nordicblocks-$current_version.zip"
echo " - update: $root_dir/dist/nordicblocks/updates/$current_version/nordicblocks-update-$current_version.zip"
echo " - staging: $root_dir/staging/nordicblocks-public-repo"