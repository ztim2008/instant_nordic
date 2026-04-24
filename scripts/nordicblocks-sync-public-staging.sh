#!/usr/bin/env bash
set -euo pipefail

root_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
source_dir="$root_dir/packages/nordicblocks"
staging_dir="$root_dir/staging/nordicblocks-public-repo"
dist_dir="$root_dir/dist"
version="$(tr -d '[:space:]' < "$source_dir/VERSION")"

if [[ ! -d "$source_dir/package" ]]; then
    echo "NordicBlocks product source is incomplete: $source_dir" >&2
    exit 1
fi

mkdir -p "$staging_dir"
rm -rf "$staging_dir/docs/blocks" "$staging_dir/docs/roadmap" "$staging_dir/dist"
rm -f "$staging_dir/dist/nordicblocks.zip" "$staging_dir/dist/nordicblocks-update.zip"
mkdir -p "$staging_dir/docs/install" "$staging_dir/docs/releases"
mkdir -p "$staging_dir/scripts" "$staging_dir/dist/nordicblocks/start" "$staging_dir/dist/nordicblocks/updates/$version"

copy_file() {
    local src="$1"
    local dest="$2"
    mkdir -p "$(dirname "$dest")"
    cp "$src" "$dest"
}

copy_dir() {
    local src="$1"
    local dest="$2"
    rm -rf "$dest"
    mkdir -p "$(dirname "$dest")"
    cp -R "$src" "$dest"
}

normalize_staging_script_paths() {
    sed -i 's|package_dir="$root_dir/packages/nordicblocks"|package_dir="$root_dir"|' "$staging_dir/scripts/build-nordicblocks-package.sh"
    sed -i 's|package_dir="$root_dir/packages/nordicblocks"|package_dir="$root_dir"|' "$staging_dir/scripts/build-nordicblocks-update-package.sh"
    sed -i 's|package_dir="$root_dir/packages/nordicblocks/package"|package_dir="$root_dir/package"|' "$staging_dir/scripts/nordicblocks-package-preflight.sh"
    sed -i 's|package_dir="$root_dir/packages/nordicblocks"|package_dir="$root_dir"|' "$staging_dir/scripts/nordicblocks-version-sync.sh"
    sed -i 's|/packages/nordicblocks/package/|/package/|g' "$staging_dir/scripts/nordicblocks-scaffold-lib.php"
    sed -i 's|/docs/nordicblocks/|/docs/blocks/|g' "$staging_dir/scripts/nordicblocks-scaffold-lib.php"
}

copy_file "$source_dir/VERSION" "$staging_dir/VERSION"
copy_file "$source_dir/manifest.ru.ini" "$staging_dir/manifest.ru.ini"
copy_file "$source_dir/install.php" "$staging_dir/install.php"
copy_file "$source_dir/install.sql" "$staging_dir/install.sql"
copy_file "$source_dir/CHANGELOG.md" "$staging_dir/CHANGELOG.md"
copy_dir "$source_dir/package" "$staging_dir/package"

copy_file "$root_dir/scripts/build-nordicblocks-package.sh" "$staging_dir/scripts/build-nordicblocks-package.sh"
copy_file "$root_dir/scripts/build-nordicblocks-update-package.sh" "$staging_dir/scripts/build-nordicblocks-update-package.sh"
copy_file "$root_dir/scripts/nordicblocks-package-preflight.sh" "$staging_dir/scripts/nordicblocks-package-preflight.sh"
copy_file "$root_dir/scripts/nordicblocks-version-sync.sh" "$staging_dir/scripts/nordicblocks-version-sync.sh"
copy_file "$root_dir/scripts/nordicblocks-scaffold-block.php" "$staging_dir/scripts/nordicblocks-scaffold-block.php"
copy_file "$root_dir/scripts/nordicblocks-scaffold-lib.php" "$staging_dir/scripts/nordicblocks-scaffold-lib.php"
copy_file "$root_dir/scripts/nordicblocks-validate-block.php" "$staging_dir/scripts/nordicblocks-validate-block.php"

if [[ -f "$root_dir/docs/nordicblocks/public-repo-template/scripts/nordicblocks-generate-release-notes.sh" ]]; then
    copy_file "$root_dir/docs/nordicblocks/public-repo-template/scripts/nordicblocks-generate-release-notes.sh" "$staging_dir/scripts/nordicblocks-generate-release-notes.sh"
fi

normalize_staging_script_paths

copy_file "$root_dir/docs/nordicblocks/INSTALLABLE-ZIP-2026-04-20.md" "$staging_dir/docs/install/INSTALLABLE-ZIP.md"
copy_file "$root_dir/docs/nordicblocks/UPDATE-RELEASE-WORKFLOW-2026-04-22.md" "$staging_dir/docs/releases/UPDATE-RELEASE-WORKFLOW.md"

if [[ -f "$root_dir/docs/nordicblocks/public-repo-template/README.md" ]]; then
    copy_file "$root_dir/docs/nordicblocks/public-repo-template/README.md" "$staging_dir/README.md"
fi

if [[ -f "$root_dir/docs/nordicblocks/public-repo-template/.gitignore" ]]; then
    copy_file "$root_dir/docs/nordicblocks/public-repo-template/.gitignore" "$staging_dir/.gitignore"
fi

if [[ -f "$root_dir/docs/nordicblocks/public-repo-template/RELEASE-LAYOUT.md" ]]; then
    copy_file "$root_dir/docs/nordicblocks/public-repo-template/RELEASE-LAYOUT.md" "$staging_dir/docs/releases/RELEASE-LAYOUT.md"
fi

if [[ -f "$root_dir/docs/nordicblocks/public-repo-template/docs/README.md" ]]; then
    copy_file "$root_dir/docs/nordicblocks/public-repo-template/docs/README.md" "$staging_dir/docs/README.md"
fi

if [[ -f "$root_dir/docs/nordicblocks/public-repo-template/.github/workflows/release.yml" ]]; then
    copy_file "$root_dir/docs/nordicblocks/public-repo-template/.github/workflows/release.yml" "$staging_dir/.github/workflows/release.yml"
fi

if [[ -f "$dist_dir/nordicblocks/start/nordicblocks-$version.zip" ]]; then
    copy_file "$dist_dir/nordicblocks/start/nordicblocks-$version.zip" "$staging_dir/dist/nordicblocks/start/nordicblocks-$version.zip"
fi

if [[ -f "$dist_dir/nordicblocks/start/nordicblocks.zip" ]]; then
    copy_file "$dist_dir/nordicblocks/start/nordicblocks.zip" "$staging_dir/dist/nordicblocks/start/nordicblocks.zip"
fi

if [[ -d "$dist_dir/nordicblocks/updates/$version" ]]; then
    copy_dir "$dist_dir/nordicblocks/updates/$version" "$staging_dir/dist/nordicblocks/updates/$version"
fi

echo "Synced NordicBlocks public staging"
echo " - source: $source_dir"
echo " - staging: $staging_dir"
echo " - version: $version"
echo "Policy: after each install/update release build, rerun this sync so staged public repo receives source, docs and dist artifacts together."