<?php

namespace App\Support;

use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PDO;
use PDOException;
use Throwable;

class Installer
{
    public function __construct(
        protected string $basePath,
    ) {}

    public static function make(): self
    {
        return new self(dirname(__DIR__, 2));
    }

    /**
     * @return array{ok: bool, checks: list<array{label: string, ok: bool, message: string}>}
     */
    public function requirements(): array
    {
        $checks = [];

        $checks[] = $this->check(
            'PHP 8.2 or newer',
            version_compare(PHP_VERSION, '8.2.0', '>='),
            PHP_VERSION,
        );

        foreach ([
            'pdo',
            'mbstring',
            'openssl',
            'tokenizer',
            'xml',
            'ctype',
            'json',
            'fileinfo',
            'curl',
        ] as $extension) {
            $checks[] = $this->check(
                $extension.' extension',
                extension_loaded($extension),
                extension_loaded($extension) ? 'Installed' : 'Missing',
            );
        }

        $checks[] = $this->check(
            'Composer dependencies',
            is_file($this->basePath.'/vendor/autoload.php'),
            is_file($this->basePath.'/vendor/autoload.php') ? 'vendor/autoload.php found' : 'Run composer install',
        );

        $checks[] = $this->check(
            '.env.example present',
            is_file($this->basePath.'/.env.example'),
            is_file($this->basePath.'/.env.example') ? 'Found' : 'Missing .env.example',
        );

        foreach ($this->writablePaths() as $path => $label) {
            $full = $this->basePath.'/'.$path;
            $checks[] = $this->check(
                $label,
                $this->ensureWritable($full),
                is_writable($full) ? 'Writable' : 'Not writable',
            );
        }

        $ok = true;
        foreach ($checks as $check) {
            if (! $check['ok']) {
                $ok = false;
                break;
            }
        }

        return ['ok' => $ok, 'checks' => $checks];
    }

    /**
     * @return array{ok: bool, message: string}
     */
    public function testDatabaseConnection(array $config): array
    {
        try {
            if ($config['connection'] === 'sqlite') {
                $database = $config['database'] ?? $this->basePath.'/database/database.sqlite';

                if (! is_file($database)) {
                    if (! is_dir(dirname($database))) {
                        mkdir(dirname($database), 0755, true);
                    }
                    touch($database);
                }

                new PDO('sqlite:'.$database);

                return ['ok' => true, 'message' => 'SQLite database is ready.'];
            }

            $dsn = sprintf(
                'mysql:host=%s;port=%s',
                $config['host'],
                $config['port'] ?? '3306',
            );

            $pdo = new PDO($dsn, $config['username'], $config['password'] ?? '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            $database = $config['database'];
            $pdo->exec(sprintf(
                'CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci',
                str_replace('`', '``', $database),
            ));

            new PDO(
                $dsn.';dbname='.$database,
                $config['username'],
                $config['password'] ?? '',
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
            );

            return ['ok' => true, 'message' => 'MySQL connection successful.'];
        } catch (PDOException $exception) {
            return ['ok' => false, 'message' => $exception->getMessage()];
        }
    }

    /**
     * @return array{ok: bool, message: string, output: list<string>}
     */
    public function installEnvironment(array $database, string $appUrl): array
    {
        $output = [];

        try {
            $this->prepareEnvFile($database, $appUrl);
            $output[] = 'Environment file written.';

            foreach ($this->writablePaths() as $path => $label) {
                $this->ensureWritable($this->basePath.'/'.$path);
            }
            $output[] = 'Storage and cache permissions verified.';

            $this->bootstrapLaravel();

            if (! config('app.key')) {
                Artisan::call('key:generate', ['--force' => true]);
                $output[] = 'Application key generated.';
            }

            Artisan::call('config:clear');
            Artisan::call('migrate', ['--force' => true]);
            $output[] = 'Database migrations completed.';
            $output = array_merge($output, $this->artisanLines(Artisan::output()));

            Artisan::call('db:seed', ['--force' => true]);
            $output[] = 'Database seeders completed.';
            $output = array_merge($output, $this->artisanLines(Artisan::output()));

            if (! is_link($this->basePath.'/public/storage') && ! is_dir($this->basePath.'/public/storage')) {
                Artisan::call('storage:link');
                $output[] = 'Public storage link created.';
            }

            return ['ok' => true, 'message' => 'Installation completed.', 'output' => $output];
        } catch (Throwable $exception) {
            return ['ok' => false, 'message' => $exception->getMessage(), 'output' => $output];
        }
    }

    /**
     * @return array{ok: bool, message: string}
     */
    public function createAdmin(string $name, string $email, string $password): array
    {
        try {
            $this->bootstrapLaravel();

            $adminRoleId = Role::query()->where('name', 'admin')->value('id');

            if (! $adminRoleId) {
                return ['ok' => false, 'message' => 'Admin role was not found. Run database seeders first.'];
            }

            if (User::query()->where('email', $email)->exists()) {
                return ['ok' => false, 'message' => 'A user with that email already exists.'];
            }

            $admin = new User([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
            ]);
            $admin->role_id = $adminRoleId;
            $admin->email_verified_at = now();
            $admin->privacy_policy_accepted_at = now();
            $admin->privacy_policy_version = config('gdpr.policy_version');
            $admin->save();

            User::query()
                ->where('email', 'admin@huddle.skullfire.co.uk')
                ->whereKeyNot($admin->id)
                ->delete();

            return ['ok' => true, 'message' => 'Administrator account created.'];
        } catch (Throwable $exception) {
            return ['ok' => false, 'message' => $exception->getMessage()];
        }
    }

    /**
     * @return array{ok: bool, checks: list<array{label: string, ok: bool, message: string, required?: bool}>}
     */
    public function readiness(): array
    {
        $checks = [];

        try {
            $this->bootstrapLaravel();

            $checks[] = $this->check(
                'Application key',
                filled(config('app.key')),
                filled(config('app.key')) ? 'Configured' : 'Missing APP_KEY',
            );

            DB::connection()->getPdo();
            $checks[] = $this->check('Database connection', true, 'Connected');

            $checks[] = $this->check(
                'Migrations',
                $this->migrationsTableExists(),
                $this->migrationsTableExists() ? 'Migrations table present' : 'Migrations missing',
            );

            $adminCount = User::query()->whereHas('role', fn ($query) => $query->where('name', 'admin'))->count();
            $checks[] = $this->check(
                'Administrator account',
                $adminCount >= 1,
                $adminCount >= 1 ? 'At least one admin exists' : 'No admin account found',
            );

            $checks[] = $this->check(
                'Legacy default admin removed',
                ! User::query()->where('email', 'admin@huddle.skullfire.co.uk')->exists(),
                User::query()->where('email', 'admin@huddle.skullfire.co.uk')->exists()
                    ? 'Default admin still present'
                    : 'Removed',
            );

            $manifestExists = is_file($this->basePath.'/public/build/manifest.json');
            $checks[] = $this->check(
                'Frontend assets',
                $manifestExists,
                $manifestExists ? 'build/manifest.json found' : 'Run npm install && npm run build',
                required: false,
            );
        } catch (Throwable $exception) {
            $checks[] = $this->check('Laravel bootstrap', false, $exception->getMessage());
        }

        $requiredOk = true;
        foreach ($checks as $check) {
            if (($check['required'] ?? true) && ! $check['ok']) {
                $requiredOk = false;
                break;
            }
        }

        return ['ok' => $requiredOk, 'checks' => $checks];
    }

    public function isInstalled(): bool
    {
        if (! is_file($this->basePath.'/.env') || ! is_file($this->basePath.'/vendor/autoload.php')) {
            return false;
        }

        if (! $this->appKey()) {
            return false;
        }

        try {
            $this->bootstrapLaravel();

            return User::query()->whereHas('role', fn ($query) => $query->where('name', 'admin'))->exists();
        } catch (Throwable) {
            return false;
        }
    }

    public function isSetupRedirectEnabled(): bool
    {
        $active = $this->htaccessPath();

        if (! is_file($active)) {
            return false;
        }

        return str_contains(file_get_contents($active), 'setup.php');
    }

    public function disableSetupRedirect(): bool
    {
        $active = $this->htaccessPath();
        $setup = $this->htaccessSetupPath();
        $installed = $this->htaccessInstalledPath();

        if (! is_file($installed)) {
            return false;
        }

        if (! $this->isSetupRedirectEnabled()) {
            return true;
        }

        if (is_file($setup)) {
            @unlink($setup);
        }

        if (is_file($active) && ! rename($active, $setup)) {
            return false;
        }

        if (! rename($installed, $active)) {
            if (is_file($setup)) {
                rename($setup, $active);
            }

            return false;
        }

        copy($active, $installed);

        return true;
    }

    protected function publicPath(): string
    {
        return $this->basePath.'/public';
    }

    protected function htaccessPath(): string
    {
        return $this->publicPath().'/.htaccess';
    }

    protected function htaccessSetupPath(): string
    {
        return $this->publicPath().'/.htaccess.setup';
    }

    protected function htaccessInstalledPath(): string
    {
        return $this->publicPath().'/.htaccess.installed';
    }

    protected function prepareEnvFile(array $database, string $appUrl): void
    {
        $envPath = $this->basePath.'/.env';

        if (! is_file($envPath)) {
            copy($this->basePath.'/.env.example', $envPath);
        }

        $values = [
            'APP_NAME' => 'Huddle',
            'APP_ENV' => 'production',
            'APP_DEBUG' => 'false',
            'APP_URL' => rtrim($appUrl, '/'),
            'DB_CONNECTION' => $database['connection'],
        ];

        if ($database['connection'] === 'sqlite') {
            $values['DB_DATABASE'] = $database['database'];
        } else {
            $values['DB_HOST'] = $database['host'];
            $values['DB_PORT'] = (string) ($database['port'] ?? '3306');
            $values['DB_DATABASE'] = $database['database'];
            $values['DB_USERNAME'] = $database['username'];
            $values['DB_PASSWORD'] = $database['password'] ?? '';
        }

        $this->writeEnvValues($values);
    }

    protected function writeEnvValues(array $values): void
    {
        foreach ($values as $key => $value) {
            $this->writeEnvValue($key, (string) $value);
        }
    }

    protected function writeEnvValue(string $key, string $value): void
    {
        $envPath = $this->basePath.'/.env';
        $content = file_get_contents($envPath);
        $escaped = Str::contains($value, [' ', '#', '"']) ? '"'.str_replace('"', '\\"', $value).'"' : $value;
        $line = $key.'='.$escaped;

        if (preg_match('/^'.preg_quote($key, '/').'=.*/m', $content)) {
            $content = preg_replace('/^'.preg_quote($key, '/').'=.*/m', $line, $content);
        } else {
            $content = rtrim($content).PHP_EOL.$line.PHP_EOL;
        }

        file_put_contents($envPath, $content);
    }

    protected function appKey(): ?string
    {
        if (! is_file($this->basePath.'/.env')) {
            return null;
        }

        if (preg_match('/^APP_KEY=(.+)$/m', file_get_contents($this->basePath.'/.env'), $matches)) {
            $value = trim($matches[1], "\"' ");

            return $value !== '' ? $value : null;
        }

        return null;
    }

    protected function bootstrapLaravel(): void
    {
        if (defined('LARAVEL_SETUP_BOOTSTRAPPED')) {
            return;
        }

        if (function_exists('app') && app() instanceof \Illuminate\Foundation\Application) {
            if (! app()->hasBeenBootstrapped()) {
                app()->make(Kernel::class)->bootstrap();
            }

            define('LARAVEL_SETUP_BOOTSTRAPPED', true);

            return;
        }

        require_once $this->basePath.'/vendor/autoload.php';

        $app = require $this->basePath.'/bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();

        define('LARAVEL_SETUP_BOOTSTRAPPED', true);
    }

    protected function migrationsTableExists(): bool
    {
        try {
            return DB::getSchemaBuilder()->hasTable('migrations');
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @return array<string, string>
     */
    protected function writablePaths(): array
    {
        return [
            'storage' => 'storage/',
            'storage/app' => 'storage/app/',
            'storage/app/public' => 'storage/app/public/',
            'storage/framework' => 'storage/framework/',
            'storage/framework/cache' => 'storage/framework/cache/',
            'storage/framework/sessions' => 'storage/framework/sessions/',
            'storage/framework/views' => 'storage/framework/views/',
            'storage/logs' => 'storage/logs/',
            'bootstrap/cache' => 'bootstrap/cache/',
            'database' => 'database/',
        ];
    }

    protected function ensureWritable(string $path): bool
    {
        if (! is_dir($path)) {
            mkdir($path, 0775, true);
        }

        if (! is_writable($path)) {
            @chmod($path, 0775);
        }

        if (! is_writable($path)) {
            @chmod($path, 0777);
        }

        return is_writable($path);
    }

    /**
     * @return list<string>
     */
    protected function artisanLines(string $output): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $output) ?: [])));
    }

    /**
     * @return array{label: string, ok: bool, message: string, required?: bool}
     */
    protected function check(string $label, bool $ok, string $message, bool $required = true): array
    {
        return compact('label', 'ok', 'message') + ['required' => $required];
    }
}
