#!/usr/bin/env sh
set -e

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
HUDDLE="$ROOT/huddle"
STAGING="$ROOT/build/staging"
OUTPUT="$ROOT/build/output"

VERSION="${1:-$(git -C "$ROOT" describe --tags --always --dirty 2>/dev/null || date +%Y%m%d)}"
PACKAGE_NAME="huddle-${VERSION}"
PACKAGE_DIR="$STAGING/$PACKAGE_NAME"

if [ ! -f "$HUDDLE/vendor/autoload.php" ]; then
    echo "Run composer install in huddle/ before building a release." >&2
    exit 1
fi

if [ ! -f "$HUDDLE/public/build/manifest.json" ]; then
    echo "Run npm run build in huddle/ before building a release." >&2
    exit 1
fi

rm -rf "$STAGING" "$OUTPUT"
mkdir -p "$PACKAGE_DIR" "$OUTPUT"

rsync -a \
    --exclude 'node_modules' \
    --exclude 'tests' \
    --exclude '.git' \
    --exclude '.github' \
    --exclude 'storage/logs/*' \
    --exclude 'storage/framework/cache/data/*' \
    --exclude 'storage/framework/sessions/*' \
    --exclude 'storage/framework/views/*' \
    --exclude 'bootstrap/cache/*.php' \
    --exclude '.env' \
    --exclude '.env.*' \
    --exclude 'database/*.sqlite' \
    --exclude '.phpunit.cache' \
    --exclude '.phpunit.result.cache' \
    --exclude 'public/hot' \
    --exclude 'auth.json' \
    "$HUDDLE/" "$PACKAGE_DIR/"

mkdir -p \
    "$PACKAGE_DIR/storage/app/private/livewire-tmp" \
    "$PACKAGE_DIR/storage/app/public" \
    "$PACKAGE_DIR/storage/framework/cache/data" \
    "$PACKAGE_DIR/storage/framework/sessions" \
    "$PACKAGE_DIR/storage/framework/views" \
    "$PACKAGE_DIR/storage/logs" \
    "$PACKAGE_DIR/bootstrap/cache"

if [ -f "$PACKAGE_DIR/public/.htaccess.setup" ]; then
    cp "$PACKAGE_DIR/public/.htaccess.setup" "$PACKAGE_DIR/public/.htaccess"
fi

cat > "$PACKAGE_DIR/DEPLOY.txt" <<'EOF'
Huddle release package
======================

Upload the contents of this folder to your server (FTP, SFTP, or file manager).

1. Point the web server document root at the public/ subdirectory.
2. Ensure storage/, bootstrap/cache/, and database/ are writable by the web server.
3. Visit https://your-domain.example/setup.php to run the installer.
4. After installation, add a cron job: * * * * * php /path/to/artisan schedule:run
5. Run a queue worker for email and background jobs: php artisan queue:work

Full instructions: hosting-guide.md in the repository .github/docs/ folder.
EOF

printf '%s\n' "$VERSION" > "$PACKAGE_DIR/VERSION"

cd "$STAGING"
zip -rq "$OUTPUT/${PACKAGE_NAME}.zip" "$PACKAGE_NAME"
tar -czf "$OUTPUT/${PACKAGE_NAME}.tar.gz" "$PACKAGE_NAME"

echo "Built $OUTPUT/${PACKAGE_NAME}.zip"
echo "Built $OUTPUT/${PACKAGE_NAME}.tar.gz"
