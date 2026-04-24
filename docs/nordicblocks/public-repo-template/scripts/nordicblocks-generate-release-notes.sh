#!/usr/bin/env bash
set -euo pipefail

root_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
version="${1:-$(tr -d '[:space:]' < "$root_dir/VERSION")}"
changelog_file="$root_dir/CHANGELOG.md"
output_file="${2:-$root_dir/dist/release-notes-$version.md}"

if [[ ! -f "$changelog_file" ]]; then
    echo "Missing changelog: $changelog_file" >&2
    exit 1
fi

mkdir -p "$(dirname "$output_file")"

awk -v version="$version" '
BEGIN {
    capture = 0
}
{
    if ($0 ~ ("^## " version "[[:space:]]*-")) {
        capture = 1
        print $0
        next
    }

    if (capture && $0 ~ /^## /) {
        exit
    }

    if (capture) {
        print $0
    }
}
' "$changelog_file" > "$output_file"

if [[ ! -s "$output_file" ]]; then
    cat > "$output_file" <<EOF
## $version

- install artifact: dist/nordicblocks/start/nordicblocks-$version.zip
- update artifact: dist/nordicblocks/updates/$version/nordicblocks-update-$version.zip
- validation: built from public repo workflow
- limitations: fill in runtime/admin/editor notes before publishing if changelog section is missing
EOF
else
    cat >> "$output_file" <<EOF

Install artifact: dist/nordicblocks/start/nordicblocks-$version.zip
Update artifact: dist/nordicblocks/updates/$version/nordicblocks-update-$version.zip
EOF
fi

echo "$output_file"