# Deployment build

GitHub Actions can produce a ready-to-upload release package with production PHP dependencies and compiled frontend assets. No Composer or Node.js is required on the server.

## What the build includes

The release package contains the `huddle/` application with:

- `vendor/` — production Composer dependencies (`--no-dev`)
- `public/build/` — compiled CSS and JavaScript from Vite
- `public/.htaccess` — setup wizard redirect (from `.htaccess.setup`)
- Empty `storage/` and `bootstrap/cache/` directories for server writes
- `DEPLOY.txt` — quick post-upload checklist

Excluded: `node_modules`, tests, `.env`, SQLite databases, log/cache/session files, and development tooling.

## Triggering a build

### Automatic (tagged releases)

Push a version tag to run the workflow and attach the zip/tar.gz to a GitHub Release:

```bash
git tag v1.0.0
git push origin v1.0.0
```

### Manual

1. Open **Actions → Release** in GitHub
2. Click **Run workflow**
3. Optionally enable **Upload build to FTP** (requires FTP secrets — see below)
4. Download the artifact from the completed workflow run

## Download and upload via FTP (Ionos, etc.)

1. Download `huddle-<version>.zip` from the workflow artifact or GitHub Release
2. Extract locally
3. Connect to your host with FTP/SFTP (FileZilla, WinSCP, or Ionos File Manager)
4. Upload the **contents** of the extracted folder to your server

### Ionos shared hosting

Typical layout:

| FTP path | Purpose |
|----------|---------|
| `/` or `/htdocs/` | Your web space root |
| Upload app files | e.g. `/huddle/` (parent of `public/`) |
| Document root | Point to `/huddle/public/` |

Ionos lets you set the document root for a domain or subdomain in the control panel. Point it at the `public/` folder inside the uploaded package — do not expose the Laravel root directory.

If you cannot change the document root, contact Ionos support or use a subdomain configured to point at `public/`.

### After upload

1. Set permissions — `storage/`, `bootstrap/cache/`, and `database/` must be writable
2. Visit `https://your-domain.example/setup.php`
3. Complete the [installer](features/setup.md)
4. Configure mail, cron, and queue worker — see the [hosting guide](hosting-guide.md)

## Automated FTP deploy

The workflow can upload directly after a manual run when FTP secrets are configured.

### GitHub secrets (Settings → Secrets → Actions)

| Secret | Example | Description |
|--------|---------|-------------|
| `FTP_SERVER` | `ftp.example.com` | FTP hostname |
| `FTP_USERNAME` | `your-ftp-user` | FTP login |
| `FTP_PASSWORD` | `••••••••` | FTP password |

Existing Flux secrets (`FLUX_USERNAME`, `FLUX_LICENSE_KEY`) are also required for the build step.

### GitHub variable (optional)

| Variable | Example | Description |
|----------|---------|-------------|
| `FTP_SERVER_DIR` | `/huddle/` | Remote directory to upload into (default: `/`) |

Use an **environment** named `Production` for FTP secrets if you want approval gates before deploy.

### Run with FTP upload

**Actions → Release → Run workflow** → enable **Upload build to FTP**.

> FTP deploys the full application tree. It does not run migrations or the setup wizard — use `/setup.php` on first deploy, and follow the [hosting guide](hosting-guide.md) for updates.

## Local build

To produce the same package on your machine:

```bash
cd huddle
composer config http-basic.composer.fluxui.dev <username> <license-key>
composer install --no-dev --optimize-autoloader
npm ci
npm run build
cd ..
sh scripts/build-release.sh
```

Output is written to `build/output/`.

## Updating an existing installation

1. Back up `.env` and the database
2. Build or download a new release package
3. Upload changed files via FTP (or run automated FTP deploy to a staging path first)
4. On the server:

```bash
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Restart queue workers after deploying.

## Related guides

- [Hosting guide](hosting-guide.md) — server requirements and web server configuration
- [Setup](features/setup.md) — web installer walkthrough
- [Docker install guide](docker-install-guide.md) — local development with Docker
