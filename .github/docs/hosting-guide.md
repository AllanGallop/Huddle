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
- **Queue worker** — run `php artisan queue:work` via systemd or supervisor for email and background jobs (`QUEUE_CONNECTION=database` by default)
- **Scheduler** — add a cron entry: `* * * * * cd /var/www/huddle/huddle && php artisan schedule:run >> /dev/null 2>&1`
- **Backups** — back up the database and `storage/app/` regularly
- **Remove setup.php access** — after confirming the install, you may delete or restrict access to `public/setup.php`; the installed `.htaccess` no longer redirects to it

### Storage symlink

If user-uploaded files (project images, branding) should be publicly accessible via `/storage`:

```bash
php artisan storage:link
```

## Updating an existing installation

```bash
cd /var/www/huddle/huddle
git pull                          # or deploy new files
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Restart queue workers after deploying code changes.

## Troubleshooting

| Problem | Solution |
|---------|----------|
| 500 error, blank page | Check `storage/logs/laravel.log`; verify permissions on `storage/` and `bootstrap/cache/` |
| Setup wizard loops | Delete `.env`, restore `.htaccess.setup`, clear browser cookies |
| CSS/JS missing | Run `npm run build`; confirm `public/build/manifest.json` exists |
| Database connection fails | Verify credentials, that the DB user has privileges, and that the host is reachable from PHP |
| Emails not sending | Configure `MAIL_*` variables and ensure a queue worker is running |

For local development workflows, see the [development guide](development.md). For Docker-based setup, see the [Docker install guide](docker-install-guide.md). For a tour of what Huddle offers, see the [features guide](features/).
