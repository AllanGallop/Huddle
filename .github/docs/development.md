# Development guide

This guide covers setting up Huddle for local development on your host machine, running tests, and common development tasks.

## Prerequisites

- PHP 8.2+ with required extensions (see [hosting guide](hosting-guide.md))
- Composer 2.x
- Node.js 22+
- SQLite (default) or MySQL/MariaDB
- A [Flux UI](https://fluxui.dev) license for Composer authentication

## Initial setup

```bash
cd huddle

# Authenticate with Flux (required before composer install)
composer config http-basic.composer.fluxui.dev <username> <license-key>

composer install
cp .env.example .env
php artisan key:generate
```

### Database

**SQLite** (default in `.env.example`):

```bash
touch database/database.sqlite
php artisan migrate
```

**MySQL / MariaDB** — update `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=huddle
DB_USERNAME=huddle
DB_PASSWORD=secret
```

Then run migrations:

```bash
php artisan migrate
```

### Frontend assets

```bash
npm install
npm run build
```

### Create an admin user

Either use the web installer or create a user via Tinker:

```bash
# Enable setup redirect
cp public/.htaccess.setup public/.htaccess

# Visit http://localhost:8000/setup.php
```

Or seed via factory in tests / Tinker after migrations have created roles.

## Running the dev server

The `composer run dev` script starts three processes concurrently:

```bash
composer run dev
```

| Process | Purpose |
|---------|---------|
| `php artisan serve` | PHP development server at [http://localhost:8000](http://localhost:8000) |
| `php artisan queue:listen` | Processes queued jobs (emails, etc.) |
| `npm run dev` | Vite dev server with hot module replacement |

### Using Docker for development

See the [Docker install guide](docker-install-guide.md). Use `DockerCompose.dev.yaml` for persistent data. You can run Vite on the host while the PHP app runs in Docker.

## Testing

Run the full CI suite (lint + tests):

```bash
composer test
```

Individual commands:

```bash
# Lint only (Laravel Pint)
composer run test:lint

# Fix lint issues
composer run lint

# PHPUnit only
php artisan test
```

Tests use an in-memory or SQLite test database configured in `phpunit.xml`. No manual database setup is needed for tests.

### Notable test areas

| Path | Covers |
|------|--------|
| `tests/Feature/Setup/` | Web installer and readiness checks |
| `tests/Feature/Admin/` | Admin panel, branding |
| `tests/Feature/Projects/` | Project access control |
| `tests/Feature/Auth/` | Authentication and 2FA |

## Code structure

```
huddle/app/
├── Livewire/          # Page components (Dashboard, Projects, Admin, etc.)
├── Models/            # Eloquent models
├── Services/          # Business logic (reports, digests, branding, exports)
├── Support/           # Installer, branding helpers, wiki utilities
├── Http/
│   ├── Controllers/   # File downloads, exports, wiki assets
│   └── Middleware/    # Admin, mentor, privacy acceptance
└── Policies/          # Authorization rules
```

### Roles and access

| Role / flag | Access |
|-------------|--------|
| **admin** (role) | Full admin panel, user management, organization settings |
| **member** (role) | Default role for regular users |
| **Mentor** (user flag) | Forms management, wiki editing, mentors area |
| **Committee** (user flag) | Financial reports |

Admins inherit mentor capabilities where middleware checks `canAccessMentors()`.

## Common tasks

### Run migrations

```bash
php artisan migrate
```

### Clear caches

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### Storage symlink

```bash
php artisan storage:link
```

### Reset to installer state

```bash
rm .env
rm -f database/database.sqlite
cp public/.htaccess.setup public/.htaccess
```

Then visit `/setup.php`.

## Environment variables

Key settings in `.env`:

| Variable | Purpose |
|----------|---------|
| `APP_URL` | Base URL for generated links |
| `APP_DEBUG` | `true` in development, `false` in production |
| `MAIL_MAILER` | Use `log` locally to capture emails in `storage/logs/` |
| `GDPR_CONTROLLER_NAME` | Shown on privacy policy page |
| `GDPR_CONTACT_EMAIL` | Privacy contact address |
| `QUEUE_CONNECTION` | `database` (default) — requires queue worker |

## CI

GitHub Actions workflows in `huddle/.github/workflows/`:

- **tests.yml** — PHPUnit on PHP 8.4 and 8.5, with asset build
- **lint.yml** — Laravel Pint

Both require `FLUX_USERNAME` and `FLUX_LICENSE_KEY` repository secrets.

## Related guides

- [Features guide](features/) — platform overview and feature walkthrough
- [Docker install guide](docker-install-guide.md) — containerized local setup
- [Hosting guide](hosting-guide.md) — production deployment on Apache/PHP
