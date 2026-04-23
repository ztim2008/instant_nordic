#!/usr/bin/env bash
set -euo pipefail

root_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
package_dir="$root_dir"

version_file="$package_dir/VERSION"
manifest_ini="$package_dir/manifest.ru.ini"

usage() {
    echo "Usage: bash scripts/nordicblocks-version-sync.sh [MAJOR.MINOR.PATCH]"
    echo "If version is omitted, it will be taken from VERSION"
}

if [[ "${1:-}" == "-h" || "${1:-}" == "--help" ]]; then
    usage
    exit 0
fi

if [[ ! -f "$version_file" || ! -f "$manifest_ini" ]]; then
    echo "Required files are missing in $package_dir" >&2
    exit 1
fi

target_version="${1:-$(tr -d '[:space:]' < "$version_file")}"

if [[ ! "$target_version" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
    echo "Invalid SemVer: '$target_version'" >&2
    exit 1
fi

IFS='.' read -r major minor patch <<< "$target_version"

echo "$target_version" > "$version_file"

tmp_ini="$(mktemp)"
awk -v major="$major" -v minor="$minor" -v patch="$patch" -v semver="$target_version" '
BEGIN {
    in_version = 0
    seen_version = 0
    done_major = 0
    done_minor = 0
    done_build = 0
    done_semver = 0
}
{
    if ($0 ~ /^\[version\][[:space:]]*$/) {
        seen_version = 1
        in_version = 1
        print
        next
    }

    if (in_version && $0 ~ /^\[[^]]+\][[:space:]]*$/) {
        if (!done_major) print "major = " major
        if (!done_minor) print "minor = " minor
        if (!done_build) print "build = " patch
        if (!done_semver) print "semver = \"" semver "\""
        in_version = 0
    }

    if (in_version) {
        if ($0 ~ /^major[[:space:]]*=/) {
            print "major = " major
            done_major = 1
            next
        }
        if ($0 ~ /^minor[[:space:]]*=/) {
            print "minor = " minor
            done_minor = 1
            next
        }
        if ($0 ~ /^build[[:space:]]*=/) {
            print "build = " patch
            done_build = 1
            next
        }
        if ($0 ~ /^semver[[:space:]]*=/) {
            print "semver = \"" semver "\""
            done_semver = 1
            next
        }
    }

    print
}
END {
    if (in_version) {
        if (!done_major) print "major = " major
        if (!done_minor) print "minor = " minor
        if (!done_build) print "build = " patch
        if (!done_semver) print "semver = \"" semver "\""
    }

    if (!seen_version) {
        print ""
        print "[version]"
        print "major = " major
        print "minor = " minor
        print "build = " patch
        print "semver = \"" semver "\""
    }
}
' "$manifest_ini" > "$tmp_ini"
mv "$tmp_ini" "$manifest_ini"

echo "Synced nordicblocks version: $target_version"
echo " - $version_file"
echo " - $manifest_ini"