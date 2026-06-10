# First-time setup

Huddle includes a browser-based installer at `/setup.php` for deployments without a separate provisioning tool.

[← Back to features](README.md)

## Setup wizard

1. **Requirements** — PHP version, extensions, writable directories
2. **Database** — SQLite or MySQL/MariaDB connection test and migration
3. **Admin** — Create the first administrator account (minimum 12-character password)

After setup, the installer swaps `.htaccess` to the installed configuration, disables itself, and redirects to `/login`.

## Deployment guides

| Guide | Use case |
|-------|----------|
| [Deployment build](../deployment.md) | GitHub Actions package for FTP upload (Ionos, etc.) |
| [Docker install guide](../docker-install-guide.md) | Local development and installer testing |
| [Hosting guide](../hosting-guide.md) | Production deployment on Apache/PHP |
