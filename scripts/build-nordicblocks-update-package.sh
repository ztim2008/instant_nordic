#!/usr/bin/env bash
set -euo pipefail

root_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
dist_dir="$root_dir/dist"
structured_dist_dir="$dist_dir/nordicblocks"
package_dir="$root_dir"
version_sync_script="$root_dir/scripts/nordicblocks-version-sync.sh"
preflight_script="$root_dir/scripts/nordicblocks-package-preflight.sh"
version_file="$package_dir/VERSION"
manifest_file="$package_dir/manifest.ru.ini"
installer_file="$package_dir/install.php"
install_sql="$package_dir/install.sql"
payload_dir="$package_dir/package"

if [[ ! -x "$version_sync_script" ]]; then
    chmod +x "$version_sync_script"
fi

if [[ -n "${1:-}" ]]; then
    bash "$version_sync_script" "$1" >/dev/null
else
    bash "$version_sync_script" >/dev/null
fi

if [[ ! -x "$preflight_script" ]]; then
    chmod +x "$preflight_script"
fi

bash "$preflight_script"

version="$(tr -d '[:space:]' < "$version_file")"
updates_dir="$structured_dist_dir/updates/$version"
structured_update_archive_path="$updates_dir/nordicblocks-update.zip"
structured_versioned_update_archive_path="$updates_dir/nordicblocks-update-$version.zip"

if [[ ! "$version" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
    echo "Invalid SemVer in $version_file: '$version'" >&2
    exit 1
fi

if [[ ! -f "$manifest_file" || ! -f "$installer_file" || ! -f "$install_sql" || ! -d "$payload_dir" ]]; then
    echo "NordicBlocks package source is incomplete" >&2
    exit 1
fi

mkdir -p "$dist_dir"
mkdir -p "$updates_dir"

tmp_dir="$(mktemp -d)"
trap 'rm -rf "$tmp_dir"' EXIT

cp "$manifest_file" "$tmp_dir/manifest.ru.ini"
cp "$installer_file" "$tmp_dir/install.php"
cp "$install_sql" "$tmp_dir/install.sql"
cp -R "$payload_dir" "$tmp_dir/package"

# InstantCMS gives priority to [install], so update artifacts must ship an update-only manifest.
tmp_manifest="$(mktemp)"
awk '
BEGIN {
    skip_install = 0
}
{
    if ($0 ~ /^\[install\][[:space:]]*$/) {
        skip_install = 1
        next
    }

    if (skip_install && $0 ~ /^\[[^]]+\][[:space:]]*$/) {
        skip_install = 0
    }

    if (!skip_install) {
        print
    }
}
' "$tmp_dir/manifest.ru.ini" > "$tmp_manifest"
mv "$tmp_manifest" "$tmp_dir/manifest.ru.ini"

# Keep update archives runtime-only even if working docs appear in payload later.
find "$tmp_dir/package" -type d \( -name docs -o -name .github \) -prune -exec rm -rf {} +
find "$tmp_dir/package" -type f \( -name '*.md' -o -name '*.txt' \) -delete

rm -f "$dist_dir/nordicblocks-update.zip"
rm -f "$dist_dir/nordicblocks-update-$version.zip"
rm -f "$structured_update_archive_path"
rm -f "$structured_versioned_update_archive_path"

(
    cd "$tmp_dir"
    zip -qr "$dist_dir/nordicblocks-update.zip" manifest.ru.ini install.php install.sql package
)

cp "$dist_dir/nordicblocks-update.zip" "$dist_dir/nordicblocks-update-$version.zip"
cp "$dist_dir/nordicblocks-update.zip" "$structured_update_archive_path"
cp "$dist_dir/nordicblocks-update.zip" "$structured_versioned_update_archive_path"

echo "$dist_dir/nordicblocks-update.zip"
echo "$dist_dir/nordicblocks-update-$version.zip"
echo "$structured_update_archive_path"
echo "$structured_versioned_update_archive_path"