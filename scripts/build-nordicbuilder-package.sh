#!/usr/bin/env bash
set -euo pipefail

root_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
package_dir="$root_dir/packages/nordicbuilder"
dist_dir="$root_dir/dist"

manifest_file="$package_dir/manifest.ru.ini"
manifest_json="$package_dir/manifest.json"
installer_file="$package_dir/install.php"
install_sql="$package_dir/install.sql"
migrations_dir="$package_dir/migrations"
version_file="$package_dir/VERSION"
changelog_file="$package_dir/CHANGELOG.md"
payload_dir="$package_dir/package"

if ! command -v zip >/dev/null 2>&1; then
    echo "zip command is required to build nordicbuilder package" >&2
    exit 1
fi

if [[ ! -f "$manifest_file" ]]; then
    echo "Missing manifest.ru.ini: $manifest_file" >&2
    exit 1
fi

if [[ ! -f "$manifest_json" ]]; then
    echo "Missing manifest.json: $manifest_json" >&2
    exit 1
fi

if [[ ! -f "$installer_file" ]]; then
    echo "Missing install.php: $installer_file" >&2
    exit 1
fi

if [[ ! -f "$install_sql" ]]; then
    echo "Missing install.sql: $install_sql" >&2
    exit 1
fi

if [[ ! -d "$migrations_dir" ]]; then
    echo "Missing migrations directory: $migrations_dir" >&2
    exit 1
fi

if [[ ! -f "$version_file" ]]; then
    echo "Missing VERSION: $version_file" >&2
    exit 1
fi

if [[ ! -f "$changelog_file" ]]; then
    echo "Missing CHANGELOG.md: $changelog_file" >&2
    exit 1
fi

if [[ ! -d "$payload_dir" ]]; then
    echo "Missing package payload directory: $payload_dir" >&2
    exit 1
fi

version="$(tr -d '[:space:]' < "$version_file")"

if [[ ! "$version" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
    echo "Invalid SemVer in VERSION ($version_file): '$version'" >&2
    exit 1
fi

archive_name="nordicbuilder.zip"
archive_path="$dist_dir/$archive_name"
versioned_archive_path="$dist_dir/nordicbuilder-$version.zip"

mkdir -p "$dist_dir"

tmp_dir="$(mktemp -d)"
trap 'rm -rf "$tmp_dir"' EXIT

mkdir -p "$tmp_dir/components/nordicbuilder"

cp "$manifest_json" "$tmp_dir/manifest.json"
cp "$installer_file" "$tmp_dir/install.php"

cp "$manifest_file" "$tmp_dir/components/nordicbuilder/manifest.ru.ini"
cp "$manifest_json" "$tmp_dir/components/nordicbuilder/manifest.json"
cp "$installer_file" "$tmp_dir/components/nordicbuilder/install.php"
cp "$install_sql" "$tmp_dir/components/nordicbuilder/install.sql"
cp "$version_file" "$tmp_dir/components/nordicbuilder/VERSION"
cp "$changelog_file" "$tmp_dir/components/nordicbuilder/CHANGELOG.md"
cp -R "$migrations_dir" "$tmp_dir/components/nordicbuilder/migrations"
cp -R "$payload_dir" "$tmp_dir/components/nordicbuilder/package"

rm -f "$archive_path"
rm -f "$versioned_archive_path"

(
    cd "$tmp_dir"
    zip -qr "$archive_path" components install.php manifest.json
)

cp "$archive_path" "$versioned_archive_path"

echo "$archive_path"
echo "$versioned_archive_path"