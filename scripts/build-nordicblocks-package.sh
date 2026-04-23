#!/usr/bin/env bash
set -euo pipefail

root_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
package_dir="$root_dir"
dist_dir="$root_dir/dist"
structured_dist_dir="$dist_dir/nordicblocks"
start_dir="$structured_dist_dir/start"
preflight_script="$root_dir/scripts/nordicblocks-package-preflight.sh"

manifest_file="$package_dir/manifest.ru.ini"
installer_file="$package_dir/install.php"
install_sql="$package_dir/install.sql"
payload_dir="$package_dir/package"

if ! command -v zip >/dev/null 2>&1; then
    echo "zip command is required to build nordicblocks package" >&2
    exit 1
fi

if [[ ! -f "$manifest_file" ]]; then
    echo "Missing manifest.ru.ini: $manifest_file" >&2
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

if [[ ! -d "$payload_dir" ]]; then
    echo "Missing package payload directory: $payload_dir" >&2
    exit 1
fi

if [[ ! -x "$preflight_script" ]]; then
    chmod +x "$preflight_script"
fi

bash "$preflight_script"

php_bin="/opt/php84/bin/php"
if [[ ! -x "$php_bin" ]]; then
    php_bin="$(command -v php)"
fi

version="$($php_bin -r '
$manifest = parse_ini_file($argv[1], true);
if (!is_array($manifest)) {
    fwrite(STDERR, "Failed to parse manifest.ru.ini\n");
    exit(1);
}
$semver = trim((string)($manifest["version"]["semver"] ?? ""));
if ($semver !== "") {
    echo $semver;
    exit(0);
}
$major = (string)($manifest["version"]["major"] ?? "0");
$minor = (string)($manifest["version"]["minor"] ?? "0");
$build = (string)($manifest["version"]["build"] ?? "0");
echo $major . "." . $minor . "." . $build;
' "$manifest_file")"

if [[ ! "$version" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
    echo "Invalid SemVer in manifest.ru.ini: '$version'" >&2
    exit 1
fi

archive_name="nordicblocks.zip"
archive_path="$dist_dir/$archive_name"
versioned_archive_path="$dist_dir/nordicblocks-$version.zip"
structured_archive_path="$start_dir/$archive_name"
structured_versioned_archive_path="$start_dir/nordicblocks-$version.zip"

mkdir -p "$dist_dir"
mkdir -p "$start_dir"

tmp_dir="$(mktemp -d)"
trap 'rm -rf "$tmp_dir"' EXIT

cp "$manifest_file" "$tmp_dir/manifest.ru.ini"
cp "$installer_file" "$tmp_dir/install.php"
cp "$install_sql" "$tmp_dir/install.sql"
cp -R "$payload_dir" "$tmp_dir/package"

# Keep installable archives runtime-only even if working docs appear in payload later.
find "$tmp_dir/package" -type d \( -name docs -o -name .github \) -prune -exec rm -rf {} +
find "$tmp_dir/package" -type f \( -name '*.md' -o -name '*.txt' \) -delete

rm -f "$archive_path"
rm -f "$versioned_archive_path"
rm -f "$structured_archive_path"
rm -f "$structured_versioned_archive_path"

(
    cd "$tmp_dir"
    zip -qr "$archive_path" manifest.ru.ini install.php install.sql package
)

cp "$archive_path" "$versioned_archive_path"
cp "$archive_path" "$structured_archive_path"
cp "$archive_path" "$structured_versioned_archive_path"

echo "$archive_path"
echo "$versioned_archive_path"
echo "$structured_archive_path"
echo "$structured_versioned_archive_path"