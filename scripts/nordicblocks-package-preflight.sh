#!/usr/bin/env bash
set -euo pipefail

root_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
package_dir="$root_dir/package"

required_payload_files=(
    "system/widgets/nordicblocks_block/widget.php"
    "system/widgets/nordicblocks_block/options.form.php"
    "templates/default/widgets/nordicblocks_block/nordicblocks_block.tpl.php"
)

missing_files=()

for relative_path in "${required_payload_files[@]}"; do
    if [[ ! -f "$package_dir/$relative_path" ]]; then
        missing_files+=("$relative_path")
    fi
done

if (( ${#missing_files[@]} > 0 )); then
    echo "NordicBlocks package preflight failed. Missing payload files:" >&2
    for relative_path in "${missing_files[@]}"; do
        echo " - $relative_path" >&2
    done
    exit 1
fi
