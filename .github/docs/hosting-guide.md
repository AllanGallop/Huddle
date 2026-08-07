# Hosting guide

This guide covers deploying Huddle on a traditional web server without Docker. Huddle includes a browser-based installer that handles environment configuration, database migrations, and admin account creation.

## Server requirements

| Requirement | Details |
|-------------|---------|
| PHP | 8.2 or newer (8.4 recommended) |
| Web server | Apache with `mod_rewrite` (recommended), or nginx with equivalent rewrite rules |
| Database | SQLite, MySQL 8+, or MariaDB 10+ |
| Composer | 2.x (run on the server or build artifacts locally) |
| Node.js | 22+ (only needed to build frontend assets) |

### Required PHP extensions

`pdo`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`, `curl`, `gd`, `intl`

For MySQL/MariaDB, also ensure `pdo_mysql` is installed. For SQLite, `pdo_sqlite`.

## Deployment overview

```
1. Upload files
2. Install Composer dependencies
3. Build frontend assets
4. Configure web server document root
5. Set directory permissions
6. Run the web installer at /setup.php
7. Harden production settings
8. Configure cron (community digest scheduler)
```

### Pre-built release package

To skip steps 2 and 3 on the server, use the [deployment build](deployment.md) workflow. It produces a zip with `vendor/` and compiled assets ready for FTP upload to Ionos or similar hosts.

## Step 1: Upload files

Clone or copy the repository to your server. The web server's document root must point to `huddle/public/`, not the repository root.

```
/var/www/huddle/          ← repository root
/var/www/huddle/huddle/   ← Laravel application
/var/www/huddle/huddle/public/  ← document root
```

Only `huddle/public/` should be web-accessible. Ensure `huddle/.env`, `huddle/storage/`, and `huddle/vendor/` are not served directly.

## Step 2: Install dependencies

On the server (or in a build step):

```bash
cd /var/www/huddle/huddle
composer install --no-dev --optimize-autoloader
```

Flux UI requires Composer authentication:

```bash
composer config http-basic.composer.fluxui.dev <username> <license-key>
```

## Step 3: Build frontend assets

```bash
cd /var/www/huddle/huddle
npm ci
npm run build
```

This produces `public/build/manifest.json` and compiled CSS/JS. The installer treats missing build assets as a warning, not a blocker, but the UI will not style correctly without them.

## Step 4: Configure the web server

### Apache

Point the virtual host `DocumentRoot` to `huddle/public`. Enable `mod_rewrite` and `AllowOverride All` so Laravel's `.htaccess` rules apply.

Example:

```apache
<VirtualHost *:80>
    ServerName huddle.example.org
    DocumentRoot /var/www/huddle/huddle/public

    <Directory /var/www/huddle/huddle/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Before installation, ensure `public/.htaccess` is the setup version:

```bash
cp public/.htaccess.setup public/.htaccess
```

After installation completes, the setup wizard automatically switches to `public/.htaccess.installed`.

### nginx

Use a configuration equivalent to Laravel's standard nginx setup. During first-time setup, add a rule to route uninstalled requests to `setup.php`:

```nginx
server {
    listen 80;
    server_name huddle.example.org;
    root /var/www/huddle/huddle/public;
    index index.php;

    # First-time setup (remove after installation)
    location / {
        if (-f $document_root/setup.php) {
            rewrite ^/(?!setup\.php)(.*)$ /setup.php last;
        }
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

Remove the setup rewrite block after installation is complete.

## Step 5: Set permissions

The web server user (e.g. `www-data`) needs write access to:

- `storage/`
- `bootstrap/cache/`
- `database/` (if using SQLite)
- `public/` (for uploaded branding assets and the `.htaccess` swap during install)

```bash
chown -R www-data:www-data storage bootstrap/cache database public
chmod -R 775 storage bootstrap/cache database public
```

## Step 6: Run the web installer

1. Open `https://your-domain.example/setup.php` in a browser
2. **Requirements** — resolve any failed checks before continuing
3. **Database** — choose SQLite or MySQL/MariaDB and test the connection
4. **Admin** — create the first administrator (password must be at least 12 characters)

The installer will:

- Create `.env` from `.env.example`
- Generate `APP_KEY`
- Run database migrations
- Swap `.htaccess` to the installed configuration
- Redirect to `/login`

### Database options

**SQLite** (simplest for small deployments):

- The installer creates `database/database.sqlite` automatically
- No separate database server required
- Ensure the `database/` directory is writable

**MySQL / MariaDB** (recommended for production):

| Setting | Example |
|---------|---------|
| Host | `127.0.0.1` or your DB host |
| Port | `3306` |
| Database | `huddle` |
| Username | dedicated DB user |
| Password | strong password |

Create the database and user beforehand:

```sql
CREATE DATABASE huddle CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'huddle'@'localhost' IDENTIFIED BY 'your-secure-password';
GRANT ALL PRIVILEGES ON huddle.* TO 'huddle'@'localhost';
FLUSH PRIVILEGES;
```

## Step 7: Production hardening

After installation, review these settings in `.env`:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example

# Mail — required for invitations, digests, and document emails
MAIL_MAILER=smtp
MAIL_HOST=...
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS=noreply@your-domain.example
MAIL_FROM_NAME="${APP_NAME}"

# GDPR / privacy page
GDPR_CONTROLLER_NAME="Your Organization"
GDPR_CONTACT_EMAIL=privacy@your-domain.example
```

### Additional recommendations

- **HTTPS** — terminate TLS at your reverse proxy or web server
- **Queue worker** — run `php artisan queue:work` via systemd or supervisor for background jobs (`QUEUE_CONNECTION=database` by default). The community digest sends mail synchronously, so a worker is not required for digests alone, but invitations and other queued work still need one (or set `QUEUE_CONNECTION=sync` on small shared hosts)
- **Scheduler (cron)** — required for the [community digest](features/community-digest.md); see [Step 8](#step-8-configure-cron-scheduler) below
- **Backups** — back up the database and `storage/app/` regularly
- **Remove setup.php access** — after confirming the install, you may delete or restrict access to `public/setup.php`; the installed `.htaccess` no longer redirects to it

### Storage symlink

If user-uploaded files (project images, branding) should be publicly accessible via `/storage`:

```bash
php artisan storage:link
```

## Step 8: Configure cron (scheduler)

Huddle uses Laravel’s scheduler to send the [community digest](features/community-digest.md) every **Saturday at 09:00** (server timezone). Cron must call `schedule:run` every minute; Laravel decides when to run `digest:send`.

### Timezone

`9:00` uses the app timezone from `.env` / `config/app.php` (`APP_TIMEZONE`, default `UTC`). Set it to your local zone if members expect Saturday morning in UK time, for example:

```dotenv
APP_TIMEZONE=Europe/London
```

### VPS / dedicated server

Edit the crontab for a user that can read the app and write to `storage/`:

```bash
crontab -e
```

Add:

```cron
* * * * * cd /var/www/huddle/huddle && php artisan schedule:run >> /dev/null 2>&1
```

Adjust the path and PHP binary if needed (for example `php8.4` or `/usr/bin/php`).

### Shared hosting (Ionos and similar)

Many shared hosts expose cron in the control panel rather than `crontab -e`.

1. Open **Cron Jobs** (Ionos: Hosting → your package → **Cron Jobs**, or via SSH if allowed)
2. Schedule the job to run **every minute** (`* * * * *`)
3. Use the host’s PHP CLI binary and the absolute path to `artisan`

Example command (replace paths and PHP version with what your host provides):

```bash
cd $HOME/huddle && php8.5 artisan schedule:run >> /dev/null 2>&1
```

Or with an absolute PHP path:

```bash
/usr/bin/php8.5 /homepages/.../huddle/artisan schedule:run >> /dev/null 2>&1
```

Tips for Ionos-style hosts:

- Prefer the same PHP major version the site uses (for example `php8.5`)
- The working directory must be the Laravel root (the folder that contains `artisan`), not `public/`
- If the panel only allows intervals coarser than every minute, use the finest available (hourly is not enough for a reliable Saturday 09:00 window; every minute is strongly preferred)
- Mail must already be configured in `.env` (`MAIL_*`) or digests will fail silently / log errors

### Verify

List scheduled tasks:

```bash
cd /path/to/huddle   # Laravel root (contains artisan)
php artisan schedule:list
```

You should see `digest:send` set for Saturdays at 09:00.

Send a one-off test (skips the “nothing new” filter):

```bash
php artisan digest:send --force
# optional: php artisan digest:send --user=1 --force
```

Check `storage/logs/laravel.log` if mail does not arrive.

## Updating an existing installation

### Pre-built release package (recommended for shared hosts)

On your local machine, download the latest GitHub Release zip:

```bash
./scripts/download-latest-release.sh
# Windows: .\scripts\download-latest-release.ps1
```

Extract and upload the contents via FTP/SFTP. Keep your existing `.env`, `storage/`, and database files.

On the server (SSH), from the application root:

```bash
./scripts/migrate.sh
# Windows: .\scripts\migrate.ps1
```

This runs `migrate --force`, `db:seed --force`, and rebuilds config/route/view caches.

### Source-based install

```bash
cd /var/www/huddle/huddle
git pull
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan db:seed --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Restart queue workers after deploying code changes.

## Troubleshooting

| Problem | Solution |
|---------|----------|
| 500 error, blank page | Check `storage/logs/laravel.log`; verify permissions on `storage/` and `bootstrap/cache/` |
| Pretty URLs 500 but `/index.php/login` works | Ionos rewrite issue — ensure `public/.htaccess` uses the PATH_INFO rule (`index.php%{REQUEST_URI}`) from this repo; remove any `Options` directives if the host forbids them |
| Setup wizard loops | Delete `.env`, restore `.htaccess.setup`, clear browser cookies |
| CSS/JS missing | Run `npm run build`; confirm `public/build/manifest.json` exists |
| Database connection fails | Verify credentials, that the DB user has privileges, and that the host is reachable from PHP |
| Digests not sending | Confirm cron runs `php artisan schedule:run` every minute; check `php artisan schedule:list`; verify `MAIL_*` and `APP_TIMEZONE`; try `php artisan digest:send --force` |
| Emails not sending | Configure `MAIL_*` variables; for queued mail ensure a queue worker is running, or use `QUEUE_CONNECTION=sync` |

For local development workflows, see the [development guide](development.md). For Docker-based setup, see the [Docker install guide](docker-install-guide.md). For a tour of what Huddle offers, see the [features guide](features/).
