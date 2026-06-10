# Docker install guide

Huddle ships with Docker Compose configurations for local development and installer testing. The application runs in a PHP 8.4 + Apache container with an optional MariaDB 11 database.

## Prerequisites

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (or Docker Engine + Compose plugin)
- Git (to clone the repository)

## Compose files

Two compose files are provided:

| File | Purpose |
|------|---------|
| `DockerCompose.yaml` | **Fresh install mode** — resets `.env` and database on container start. Use this to test the web installer or demo a clean setup. |
| `DockerCompose.dev.yaml` | **Development mode** — persists your `.env` and database volume between restarts. Use this for day-to-day work. |

Both expose the app at [http://localhost:8000](http://localhost:8000).

## Fresh install (test the setup wizard)

The fresh-install scripts reset install artifacts on the host, tear down volumes, and start `DockerCompose.yaml`:

```bash
# Linux / macOS
./scripts/fresh-install.sh

# Windows (PowerShell)
.\scripts\fresh-install.ps1
```

Or run Compose directly:

```bash
docker compose -f DockerCompose.yaml up --build
```

On start, the entrypoint:

1. Runs `composer install` if `vendor/` is missing
2. Removes `.env` and any SQLite database file
3. Restores `public/.htaccess.setup` so requests redirect to the setup wizard

### Database defaults (fresh install)

`DockerCompose.yaml` pre-configures the setup wizard with these MySQL values:

| Setting | Value |
|---------|-------|
| Connection | MySQL |
| Host | `db` |
| Port | `3306` |
| Database | `huddle` |
| Username | `huddle` |
| Password | `secret` |

When the setup wizard asks for a database host, enter **`db`** (the Docker service name, not `localhost` or `127.0.0.1`).

### Setup wizard steps

1. **Requirements** — PHP extensions, writable directories, Composer dependencies
2. **Database** — Connection test, `.env` creation, migrations
3. **Admin** — Create the first administrator account (minimum 12-character password)

After completion, the installer swaps `.htaccess` to the installed configuration and redirects to `/login`.

## Development mode (persistent data)

For ongoing development where you want to keep your database and `.env`:

```bash
docker compose -f DockerCompose.dev.yaml up --build
```

Differences from fresh-install mode:

- `HUDDLE_FRESH_INSTALL` is `0` — no reset on container start
- MariaDB data is stored in a named Docker volume (`huddle_db`)
- Port `3306` is exposed so you can connect from database tools on the host

### First-time setup in dev mode

If you have not run the installer yet:

1. Start the containers
2. Open [http://localhost:8000](http://localhost:8000)
3. Complete the setup wizard (use host `db` for MySQL)

If `.env` already exists with a valid configuration, the app loads directly.

### Re-running the installer in dev mode

To go through setup again without using fresh-install mode:

```bash
# Stop containers
docker compose -f DockerCompose.dev.yaml down

# On the host, remove install state
rm huddle/.env
rm -f huddle/database/database.sqlite
cp huddle/public/.htaccess.setup huddle/public/.htaccess

# Start again
docker compose -f DockerCompose.dev.yaml up --build
```

To also wipe the database volume:

```bash
docker compose -f DockerCompose.dev.yaml down -v
```

## How the container works

The image is defined in `docker/Dockerfile`:

- **Base:** `php:8.4-apache`
- **Extensions:** `pdo_mysql`, `pdo_sqlite`, `mbstring`, `gd`, `intl`, `curl`, and others
- **Web root:** `/var/www/html/public` (mapped from `./huddle` via volume mount)
- **Entrypoint:** `docker/entrypoint.sh` — installs Composer deps, ensures writable directories, optionally resets install state

The `huddle/` directory is bind-mounted into the container, so code changes on the host are reflected immediately. Rebuild the image only when `docker/Dockerfile` or its copied files change.

## Building frontend assets

The Docker setup does not run Vite automatically. For production-like assets inside the container:

```bash
docker compose -f DockerCompose.dev.yaml exec app bash -c "npm install && npm run build"
```

For active frontend development, run Vite on the host instead (see the [development guide](development.md)). For a tour of what Huddle offers once installed, see the [features guide](features/).

## Troubleshooting

### "Connection refused" when testing the database

Use host **`db`**, not `localhost`. Inside the app container, `localhost` refers to the container itself, not the database service.

### Setup wizard does not appear

Check that `huddle/public/.htaccess` is the setup version:

```bash
cp huddle/public/.htaccess.setup huddle/public/.htaccess
```

Also confirm `huddle/public/setup.php` exists and no `.env` file is present (or delete `.env` to re-run setup).

### Permission errors on `storage/` or `bootstrap/cache/`

The entrypoint sets ownership to `www-data`. If issues persist:

```bash
docker compose -f DockerCompose.dev.yaml exec app chown -R www-data:www-data storage bootstrap/cache database public
docker compose -f DockerCompose.dev.yaml exec app chmod -R 775 storage bootstrap/cache database public
```

### Port 8000 already in use

Change the host port in the compose file:

```yaml
ports:
  - "8080:80"   # use http://localhost:8080
```

### Composer dependencies missing

The entrypoint runs `composer install` automatically. If it fails (e.g. Flux UI authentication), configure credentials on the host first — see the [development guide](development.md).
