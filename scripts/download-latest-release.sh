#!/usr/bin/env sh
set -e

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
REPO="AllanGallop/Huddle"
TAG=""
OUTPUT="$ROOT/build/download"
API_BASE="https://api.github.com"

usage() {
    cat <<'EOF'
Usage: download-latest-release.sh [options]

Download the latest (or a specific) Huddle release zip from GitHub.

Options:
  --repo owner/name   GitHub repository (default: AllanGallop/Huddle)
  --tag vX.Y.Z        Specific release tag instead of latest
  --output DIR        Download directory (default: build/download/)
  -h, --help          Show this help
EOF
}

while [ $# -gt 0 ]; do
    case "$1" in
        --repo)
            REPO="${2:?--repo requires owner/name}"
            shift 2
            ;;
        --tag)
            TAG="${2:?--tag requires a tag name}"
            shift 2
            ;;
        --output)
            OUTPUT="${2:?--output requires a directory}"
            shift 2
            ;;
        -h|--help)
            usage
            exit 0
            ;;
        *)
            echo "Unknown option: $1" >&2
            usage >&2
            exit 1
            ;;
    esac
done

if ! command -v curl >/dev/null 2>&1; then
    echo "curl is required." >&2
    exit 1
fi

if [ -n "$TAG" ]; then
    API_URL="$API_BASE/repos/$REPO/releases/tags/$TAG"
else
    API_URL="$API_BASE/repos/$REPO/releases/latest"
fi

echo "Fetching release info from $API_URL ..."
RELEASE_JSON="$(curl -fsSL \
    -H "Accept: application/vnd.github+json" \
    -H "X-GitHub-Api-Version: 2022-11-28" \
    "$API_URL")" || {
    echo "Failed to fetch release info. Check the repo name and that a release exists." >&2
    exit 1
}

# Prefer python for JSON; fall back to a simple grep if unavailable.
parse_asset() {
    if command -v python3 >/dev/null 2>&1; then
        printf '%s' "$RELEASE_JSON" | python3 -c '
import json, sys, re
r = json.load(sys.stdin)
tag = r.get("tag_name") or ""
asset = None
for a in r.get("assets") or []:
    name = a.get("name") or ""
    if re.match(r"huddle-.*\.zip$", name):
        asset = a
        break
if not asset:
    print("", file=sys.stderr)
    sys.exit(2)
print(tag)
print(asset["name"])
print(asset["browser_download_url"])
'
    elif command -v python >/dev/null 2>&1; then
        printf '%s' "$RELEASE_JSON" | python -c '
import json, sys, re
r = json.load(sys.stdin)
tag = r.get("tag_name") or ""
asset = None
for a in r.get("assets") or []:
    name = a.get("name") or ""
    if re.match(r"huddle-.*\.zip$", name):
        asset = a
        break
if not asset:
    print("", file=sys.stderr)
    sys.exit(2)
print(tag)
print(asset["name"])
print(asset["browser_download_url"])
'
    else
        RELEASE_TAG="$(printf '%s\n' "$RELEASE_JSON" | tr ',' '\n' | sed -n 's/.*"tag_name"[[:space:]]*:[[:space:]]*"\([^"]*\)".*/\1/p' | head -1)"
        ASSET_URL="$(printf '%s\n' "$RELEASE_JSON" | tr ',' '\n' | sed -n 's/.*"browser_download_url"[[:space:]]*:[[:space:]]*"\([^"]*huddle-[^"]*\.zip\)".*/\1/p' | head -1)"
        ASSET_NAME="$(basename "$ASSET_URL")"
        if [ -z "$ASSET_URL" ]; then
            return 2
        fi
        printf '%s\n%s\n%s\n' "$RELEASE_TAG" "$ASSET_NAME" "$ASSET_URL"
    fi
}

PARSED="$(parse_asset)" || {
    echo "No huddle-*.zip asset found on this release." >&2
    exit 1
}

RELEASE_TAG="$(printf '%s\n' "$PARSED" | sed -n '1p')"
ASSET_NAME="$(printf '%s\n' "$PARSED" | sed -n '2p')"
ASSET_URL="$(printf '%s\n' "$PARSED" | sed -n '3p')"

if [ -z "$ASSET_URL" ] || [ -z "$ASSET_NAME" ]; then
    echo "No huddle-*.zip asset found on release ${RELEASE_TAG:-unknown}." >&2
    exit 1
fi

mkdir -p "$OUTPUT"
DEST="$OUTPUT/$ASSET_NAME"

echo "Downloading $ASSET_NAME (tag: ${RELEASE_TAG:-unknown}) ..."
curl -fsSL -L -o "$DEST" \
    -H "Accept: application/octet-stream" \
    "$ASSET_URL"

echo ""
echo "Saved: $DEST"
echo ""
echo "Next steps:"
echo "  1. Extract the zip locally."
echo "  2. Upload the package contents via FTP/SFTP."
echo "  3. Do NOT overwrite .env, storage/, or existing database/*.sqlite files."
echo "  4. On the server, run: ./scripts/migrate.sh"
echo ""
