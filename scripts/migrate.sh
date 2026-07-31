#!/usr/bin/env sh
set -e

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
NO_SEED=0
NO_CACHE=0

usage() {
    cat <<'EOF'
Usage: migrate.sh [options]

Run database migrations and seeders for a Huddle installation.

Options:
  --no-seed    Skip db:seed
  --no-cache   Skip config/route/view cache rebuild
  -h, --help   Show this help
EOF
}

while [ $# -gt 0 ]; do
    case "$1" in
        --no-seed)
            NO_SEED=1
            shift
            ;;
        --no-cache)
            NO_CACHE=1
            shift
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

# Resolve app root:
# - Repo layout: scripts/ next to huddle/ → use huddle/
# - Deployed package: scripts/ inside app (or run from app root) → use app dir
resolve_app_root() {
    if [ -f "$SCRIPT_DIR/../huddle/artisan" ]; then
        printf '%s\n' "$(cd "$SCRIPT_DIR/../huddle" && pwd)"
        return
    fi
    if [ -f "$SCRIPT_DIR/artisan" ]; then
        printf '%s\n' "$SCRIPT_DIR"
        return
    fi
    if [ -f "$SCRIPT_DIR/../artisan" ]; then
        printf '%s\n' "$(cd "$SCRIPT_DIR/.." && pwd)"
        return
    fi
    if [ -f "./artisan" ]; then
        printf '%s\n' "$(pwd)"
        return
    fi
    return 1
}

APP_ROOT="$(resolve_app_root)" || {
    echo "Could not find Laravel app root (artisan). Run from the app directory or keep this script under scripts/." >&2
    exit 1
}

if [ ! -f "$APP_ROOT/vendor/autoload.php" ]; then
    echo "Missing vendor/autoload.php in $APP_ROOT - upload a full release package first." >&2
    exit 1
fi

if ! command -v php >/dev/null 2>&1; then
    echo "php is required on PATH." >&2
    exit 1
fi

cd "$APP_ROOT"
echo "App root: $APP_ROOT"
echo "Running migrations..."
php artisan migrate --force

if [ "$NO_SEED" -eq 0 ]; then
    echo "Running seeders..."
    php artisan db:seed --force
else
    echo "Skipping seeders (--no-seed)."
fi

if [ "$NO_CACHE" -eq 0 ]; then
    echo "Rebuilding caches..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
else
    echo "Skipping cache rebuild (--no-cache)."
fi

echo ""
echo "Done. Restart queue workers if they are running."
echo ""
