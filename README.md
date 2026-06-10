# Huddle

<p align="center">
  <a href="LICENSE"><img src="https://img.shields.io/badge/License-GPLv3-blue.svg" alt="License: GPL v3"></a>
  <a href="https://www.php.net/"><img src="https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white" alt="PHP 8.2+"></a>
  <a href="https://laravel.com"><img src="https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white" alt="Laravel 12"></a>
  <a href="https://livewire.laravel.com"><img src="https://img.shields.io/badge/Livewire-4-FB70A9?logo=livewire&logoColor=white" alt="Livewire 4"></a>
  <a href=".github/docs/docker-install-guide.md"><img src="https://img.shields.io/badge/Docker-ready-2496ED?logo=docker&logoColor=white" alt="Docker ready"></a>
  <a href=".github/docs/"><img src="https://img.shields.io/badge/docs-features-287878" alt="Documentation"></a>
</p>

<p align="center">
  <img src=".github/images/banner-dark.svg" alt="Huddle" width="320">
</p>

Huddle is a community management platform for organizations that run projects, events, and member programs. It provides a single place to coordinate volunteers, share knowledge, collect form responses, and manage membership.

## Features

| | |
|---|---|
| [**Projects**](.github/docs/features/projects.md) | Status tracking, volunteers, images, comments, quotes, and invoices |
| [**Events**](.github/docs/features/events.md) | Scheduling, public/private visibility, and volunteer rosters |
| [**Forms**](.github/docs/features/forms.md) | Surveys and scored exams with submission review |
| [**Wiki**](.github/docs/features/wiki.md) | Markdown knowledge base with version history |
| [**Members**](.github/docs/features/members.md) | Searchable directory with membership and tags |
| [**Mentors**](.github/docs/features/mentors.md) | Accreditation management and member assignments |
| [**Reports**](.github/docs/features/reports.md) | Filtered project status reports with PDF export |
| [**Admin**](.github/docs/features/admin.md) | Users, tags, membership, branding, and bank details |

Huddle also includes a [community digest](.github/docs/features/community-digest.md), [privacy & GDPR tools](.github/docs/features/privacy-and-gdpr.md), [two-factor authentication](.github/docs/features/user-settings.md), and a [web-based installer](.github/docs/features/setup.md).

<p align="center">
  <img src=".github/images/Screenshot_Project.png" alt="Project detail page" width="49%">
  <img src=".github/images/Screenshot_Events.png" alt="Event detail page" width="49%">
</p>

See the **[features guide](.github/docs/features/)** for a full walkthrough with screenshots.

## Quick start (Docker)

The fastest way to run Huddle locally is with Docker. A web-based setup wizard handles database configuration and admin account creation.

**Fresh install** (resets `.env` and database volume on each start — useful for testing the installer):

```bash
# Linux / macOS
./scripts/fresh-install.sh

# Windows (PowerShell)
.\scripts\fresh-install.ps1
```

Or manually:

```bash
docker compose -f DockerCompose.yaml up --build
```

Open [http://localhost:8000](http://localhost:8000). When prompted for a database host, use `db`.

**Day-to-day development** (keeps your `.env` and database between restarts):

```bash
docker compose -f DockerCompose.dev.yaml up --build
```

See the [Docker install guide](.github/docs/docker-install-guide.md) for details.

## Hosting without Docker

Huddle can be deployed on any server with PHP 8.2+, a web server (Apache recommended), and SQLite or MySQL/MariaDB. The built-in installer at `/setup.php` walks through requirements, database setup, and admin account creation.

See the [hosting guide](.github/docs/hosting-guide.md).

## Local development (without Docker)

For active development on the host machine:

```bash
cd huddle
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite   # if using SQLite
php artisan migrate
npm install
npm run build
composer run dev                 # PHP server, queue worker, and Vite
```

Flux UI is a licensed dependency. Configure Composer authentication before `composer install`:

```bash
composer config http-basic.composer.fluxui.dev <username> <license-key>
```

See the [development guide](.github/docs/development.md) for the full workflow, testing, and troubleshooting.

## Documentation

| Guide | Description |
|-------|-------------|
| [Features guide](.github/docs/features/) | Platform overview, permissions, and feature walkthrough |
| [Deployment build](.github/docs/deployment.md) | GitHub Actions release package and FTP deploy (Ionos, etc.) |
| [Docker install guide](.github/docs/docker-install-guide.md) | Running Huddle in Docker, fresh install vs dev mode |
| [Hosting guide](.github/docs/hosting-guide.md) | Deploying on Apache/PHP with the web installer |
| [Development guide](.github/docs/development.md) | Local setup, testing, linting, and common tasks |

## Requirements

| Component | Version |
|-----------|---------|
| PHP | 8.2+ (8.4 recommended) |
| Database | SQLite, MySQL, or MariaDB |
| Node.js | 22+ (for building frontend assets) |
| Composer | 2.x |

PHP extensions: `pdo`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`, `curl`, `gd`, `intl`

## Testing

```bash
cd huddle
composer test
```

This runs [Laravel Pint](https://laravel.com/docs/pint) lint checks and the PHPUnit test suite.

## License

Huddle is free software licensed under the [GNU General Public License v3.0 or later](LICENSE).

Third-party dependencies (including Laravel and Flux UI) are subject to their own licenses.