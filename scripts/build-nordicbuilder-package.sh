#!/usr/bin/env bash
set -euo pipefail

root_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
package_dir="$root_dir/packages/nordicbuilder"
dist_dir="$root_dir/dist"

manifest_file="$package_dir/manifest.ru.ini"
install_sql="$package_dir/install.sql"
payload_dir="$package_dir/package"

if ! command -v zip >/dev/null 2>&1; then
    echo "zip command is required to build nordicbuilder package" >&2
    exit 1
fi

if [[ ! -f "$manifest_file" ]]; then
    echo "Missing manifest.ru.ini: $manifest_file" >&2
    exit 1
fi

if [[ ! -f "$install_sql" ]]; then
    echo "Missing install.sql: $install_sql" >&2
    exit 1
fi

if [[ ! -d "$payload_dir" ]]; then
    echo "Missing package payload directory: $payload_dir" >&2
    exit 1
fi

major="$(awk -F '=' '/^major[[:space:]]*=/{gsub(/[[:space:]]/, "", $2); print $2}' "$manifest_file")"
minor="$(awk -F '=' '/^minor[[:space:]]*=/{gsub(/[[:space:]]/, "", $2); print $2}' "$manifest_file")"
build="$(awk -F '=' '/^build[[:space:]]*=/{gsub(/[[:space:]]/, "", $2); print $2}' "$manifest_file")"

if [[ -z "$major" || -z "$minor" || -z "$build" ]]; then
    echo "Unable to parse version from $manifest_file" >&2
    exit 1
fi

version="$major.$minor.$build"
archive_name="nordicbuilder-$version.zip"
archive_path="$dist_dir/$archive_name"

mkdir -p "$dist_dir"

tmp_dir="$(mktemp -d)"
trap 'rm -rf "$tmp_dir"' EXIT

cp "$manifest_file" "$tmp_dir/manifest.ru.ini"
cp "$install_sql" "$tmp_dir/install.sql"
cp -R "$payload_dir" "$tmp_dir/package"

rm -f "$archive_path"

(
    cd "$tmp_dir"
    zip -qr "$archive_path" manifest.ru.ini install.sql package
)

echo "$archive_path"