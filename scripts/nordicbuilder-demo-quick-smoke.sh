#!/usr/bin/env bash
set -euo pipefail

BASE_URL="${BASE_URL:-https://nordic-builder.store}"
HOME_PATH="${HOME_PATH:-/}"
LIST_PATH="${LIST_PATH:-/board}"
CATEGORY_PATH="${CATEGORY_PATH:-/board/nedvizhimost}"
ITEM_PATH="${ITEM_PATH:-/board/7-prodam-kvartiru-v-novostroike.html}"
PROFILE_PATH="${PROFILE_PATH:-/users/1}"
TRACE_PARAM="${TRACE_PARAM:-lb_trace}"
CURL_TIMEOUT="${CURL_TIMEOUT:-25}"
REQUIRE_ADMIN_TRACE="${REQUIRE_ADMIN_TRACE:-0}"
ADMIN_COOKIE="${ADMIN_COOKIE:-}"
ADMIN_COOKIE_FILE="${ADMIN_COOKIE_FILE:-}"

usage() {
    cat <<'USAGE'
Usage:
  bash scripts/nordicbuilder-demo-quick-smoke.sh

Optional env:
  BASE_URL=https://nordic-builder.store
  ADMIN_COOKIE='icms[auth]=<token>'
  ADMIN_COOKIE_FILE=/path/to/cookies.txt
  REQUIRE_ADMIN_TRACE=1

Route overrides:
  HOME_PATH=/
  LIST_PATH=/board
  CATEGORY_PATH=/board/nedvizhimost
  ITEM_PATH=/board/7-prodam-kvartiru-v-novostroike.html
  PROFILE_PATH=/users/1
USAGE
}

if [[ "${1:-}" == "-h" || "${1:-}" == "--help" ]]; then
    usage
    exit 0
fi

if [[ -n "$ADMIN_COOKIE" && -n "$ADMIN_COOKIE_FILE" ]]; then
    echo "Use either ADMIN_COOKIE or ADMIN_COOKIE_FILE, not both" >&2
    exit 2
fi

tmp_dir="$(mktemp -d)"
trap 'rm -rf "$tmp_dir"' EXIT

declare -a ROUTES=(
    "$HOME_PATH"
    "$LIST_PATH"
    "$CATEGORY_PATH"
    "$ITEM_PATH"
    "$PROFILE_PATH"
)

declare -A EXPECTED_OVERLAY=(
    ["$LIST_PATH"]=0
    ["$CATEGORY_PATH"]=1
    ["$ITEM_PATH"]=0
    ["$PROFILE_PATH"]=1
)

build_url() {
    local path="$1"
    local with_trace="$2"
    local url="${BASE_URL%/}${path}"

    if [[ "$with_trace" -eq 1 ]]; then
        if [[ "$url" == *"?"* ]]; then
            if [[ "$url" == *"?" ]]; then
                url+="${TRACE_PARAM}=1"
            else
                url+="&${TRACE_PARAM}=1"
            fi
        elif [[ "$url" == *"="* ]]; then
            url+="&${TRACE_PARAM}=1"
        else
            url+="?${TRACE_PARAM}=1"
        fi
    fi

    printf '%s' "$url"
}

request_page() {
    local path="$1"
    local role="$2"
    local with_trace="$3"
    local body_file="$4"
    local url
    url="$(build_url "$path" "$with_trace")"

    local -a curl_args
    curl_args=(
        -k
        -L
        -sS
        --max-time "$CURL_TIMEOUT"
        -o "$body_file"
        -w "%{http_code}"
    )

    if [[ "$role" == "admin" ]]; then
        if [[ -n "$ADMIN_COOKIE" ]]; then
            curl_args+=(--cookie "$ADMIN_COOKIE")
        elif [[ -n "$ADMIN_COOKIE_FILE" ]]; then
            curl_args+=(--cookie "$ADMIN_COOKIE_FILE")
        fi
    fi

    curl "${curl_args[@]}" "$url"
}

extract_trace_info() {
    local body_file="$1"
    php -r '
$body = @file_get_contents($argv[1]);
if (!is_string($body) || $body === "") {
    fwrite(STDERR, "empty-body\n");
    exit(11);
}
if (!preg_match("/lb-effective-page-trace:\\s*([A-Za-z0-9+\\/=]+)/", $body, $m)) {
    fwrite(STDERR, "trace-marker-missing\n");
    exit(12);
}
$trace_json = base64_decode($m[1], true);
if (!is_string($trace_json) || $trace_json === "") {
    fwrite(STDERR, "trace-base64-invalid\n");
    exit(13);
}
$trace = json_decode($trace_json, true);
if (!is_array($trace)) {
    fwrite(STDERR, "trace-json-invalid\n");
    exit(14);
}
$overlay = "";
$reason = "";
$branch = "";
$effective_key = "";
foreach ($trace as $entry) {
    if (!is_array($entry)) {
        continue;
    }
    $stage = isset($entry["stage"]) ? (string) $entry["stage"] : "";
    $payload = isset($entry["payload"]) && is_array($entry["payload"]) ? $entry["payload"] : [];

    if ($stage === "template.route-context") {
        if (array_key_exists("uses_overlay_hooks", $payload)) {
            $overlay = !empty($payload["uses_overlay_hooks"]) ? "1" : "0";
        } elseif (array_key_exists("is_overlay_context", $payload)) {
            $overlay = !empty($payload["is_overlay_context"]) ? "1" : "0";
        }
    }
    if ($stage === "template.takeover-skip" && isset($payload["reason"])) {
        $reason = (string) $payload["reason"];
    }
    if ($stage === "template.effective-page-key" && isset($payload["effective_page_key"])) {
        $effective_key = (string) $payload["effective_page_key"];
    }
    if ($stage === "template.takeover-applied" || $stage === "template.takeover-skip") {
        $branch = $stage;
    }
}

echo "overlay:" . $overlay . "\n";
echo "reason:" . $reason . "\n";
echo "branch:" . $branch . "\n";
echo "effective_key:" . $effective_key . "\n";
' "$body_file"
}

format_trace_summary() {
    local overlay="$1"
    local reason="$2"
    local branch="$3"
    local effective_key="$4"
    local effective_display

    effective_display="$effective_key"
    if [[ -z "$effective_display" ]]; then
        effective_display="-"
    fi

    printf 'overlay=%s, reason=%s, branch=%s, effective=%s' "${overlay:-?}" "${reason:-?}" "${branch:-?}" "$effective_display"
}

run_role_checks() {
    local role="$1"
    local with_trace="$2"
    local failures_ref="$3"

    for path in "${ROUTES[@]}"; do
        local safe_name
        safe_name="$(echo "$path" | sed 's#[^a-zA-Z0-9]#_#g')"
        local body_file="$tmp_dir/${role}${safe_name}.html"
        local http_code
        http_code="$(request_page "$path" "$role" "$with_trace" "$body_file")"

        local note="ok"
        if [[ "$http_code" != "200" ]]; then
            note="HTTP $http_code"
            printf '| %s | `%s` | `%s` | %s |\n' "$role" "$path" "$http_code" "$note"
            eval "$failures_ref=$(( $failures_ref + 1 ))"
            continue
        fi

        if [[ "$with_trace" -eq 1 ]]; then
            local trace_info
            if ! trace_info="$(extract_trace_info "$body_file" 2>/dev/null)"; then
                note="trace-missing"
                printf '| %s | `%s` | `%s` | %s |\n' "$role" "$path" "$http_code" "$note"
                eval "$failures_ref=$(( $failures_ref + 1 ))"
                continue
            fi

            local overlay=""
            local reason=""
            local branch=""
            local effective_key=""
            while IFS=':' read -r key value; do
                case "$key" in
                    overlay)
                        overlay="$value"
                        ;;
                    reason)
                        reason="$value"
                        ;;
                    branch)
                        branch="$value"
                        ;;
                    effective_key)
                        effective_key="$value"
                        ;;
                esac
            done <<<"$trace_info"

            note="$(format_trace_summary "$overlay" "$reason" "$branch" "$effective_key")"

            if [[ -n "${EXPECTED_OVERLAY[$path]+x}" ]]; then
                local expected_overlay="${EXPECTED_OVERLAY[$path]}"
                if [[ "$overlay" != "$expected_overlay" ]]; then
                    note+="; expected-overlay=$expected_overlay"
                    eval "$failures_ref=$(( $failures_ref + 1 ))"
                fi
            fi

            if [[ "$path" == "$CATEGORY_PATH" || "$path" == "$PROFILE_PATH" ]]; then
                if [[ "$reason" != "overlay-or-landingbuilder-route" ]]; then
                    note+="; expected-reason=overlay-or-landingbuilder-route"
                    eval "$failures_ref=$(( $failures_ref + 1 ))"
                fi
            fi

            if [[ "$path" == "$LIST_PATH" || "$path" == "$ITEM_PATH" ]]; then
                if [[ "$reason" == "overlay-or-landingbuilder-route" ]]; then
                    note+="; expected-non-overlay-reason"
                    eval "$failures_ref=$(( $failures_ref + 1 ))"
                fi
            fi
        fi

        printf '| %s | `%s` | `%s` | %s |\n' "$role" "$path" "$http_code" "$note"
    done
}

printf 'Nordicbuilder Demo Quick Smoke\n'
printf 'Base URL: %s\n\n' "$BASE_URL"
printf '| Role | URL | HTTP | Trace summary |\n'
printf '| --- | --- | --- | --- |\n'

failures=0

run_role_checks "guest" 0 failures

admin_enabled=1
if [[ -z "$ADMIN_COOKIE" && -z "$ADMIN_COOKIE_FILE" ]]; then
    admin_enabled=0
fi

if [[ "$admin_enabled" -eq 1 ]]; then
    run_role_checks "admin" 1 failures
elif [[ "$REQUIRE_ADMIN_TRACE" == "1" ]]; then
    echo "Admin trace checks are required, but ADMIN_COOKIE/ADMIN_COOKIE_FILE is not set" >&2
    exit 3
else
    echo
    echo "Admin trace checks were skipped (cookie is not provided)."
fi

echo
if [[ "$failures" -eq 0 ]]; then
    echo "STATUS: PASS"
    exit 0
fi

echo "STATUS: FAIL (${failures} checks failed)"
exit 1
