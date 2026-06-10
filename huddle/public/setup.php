<?php

declare(strict_types=1);

$basePath = dirname(__DIR__);
$autoload = $basePath.'/vendor/autoload.php';

if (! is_file($autoload)) {
    http_response_code(500);
    exit('Composer dependencies are missing. Run "composer install" in the project root, then reload this page.');
}

require_once $autoload;

use App\Support\Installer;

define('SETUP_START', microtime(true));

session_start();

$installer = Installer::make();
$step = $_POST['step'] ?? $_GET['step'] ?? 'requirements';
$errors = [];
$messages = [];
$appUrl = rtrim((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'], '/');

if ($installer->isInstalled() && $step !== 'complete') {
    $installer->disableSetupRedirect();
    header('Location: /login');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['_token'] ?? '';
    if (! is_string($token) || ! hash_equals($_SESSION['_setup_token'] ?? '', $token)) {
        http_response_code(419);
        exit('Invalid session token. Refresh the page and try again.');
    }
}

if (! isset($_SESSION['_setup_token'])) {
    $_SESSION['_setup_token'] = bin2hex(random_bytes(32));
}

$csrf = $_SESSION['_setup_token'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 'database') {
    $connection = $_POST['db_connection'] ?? 'sqlite';

    $database = [
        'connection' => $connection,
        'database' => $connection === 'sqlite'
            ? ($basePath.'/database/database.sqlite')
            : trim((string) ($_POST['db_database'] ?? '')),
        'host' => trim((string) ($_POST['db_host'] ?? '127.0.0.1')),
        'port' => trim((string) ($_POST['db_port'] ?? '3306')),
        'username' => trim((string) ($_POST['db_username'] ?? '')),
        'password' => (string) ($_POST['db_password'] ?? ''),
    ];

    $test = $installer->testDatabaseConnection($database);

    if (! $test['ok']) {
        $errors[] = $test['message'];
        $step = 'database';
    } else {
        $result = $installer->installEnvironment($database, $appUrl);

        if (! $result['ok']) {
            $errors[] = $result['message'];
            $step = 'database';
        } else {
            $messages = $result['output'];
            $_SESSION['setup_database_complete'] = true;
            $step = 'admin';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 'admin') {
    if (! ($_SESSION['setup_database_complete'] ?? false)) {
        header('Location: setup.php?step=requirements');
        exit;
    }

    $name = trim((string) ($_POST['admin_name'] ?? ''));
    $email = trim((string) ($_POST['admin_email'] ?? ''));
    $password = (string) ($_POST['admin_password'] ?? '');
    $passwordConfirmation = (string) ($_POST['admin_password_confirmation'] ?? '');

    if ($name === '') {
        $errors[] = 'Name is required.';
    }

    if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email address is required.';
    }

    if (strlen($password) < 12) {
        $errors[] = 'Password must be at least 12 characters.';
    }

    if ($password !== $passwordConfirmation) {
        $errors[] = 'Password confirmation does not match.';
    }

    if ($errors === []) {
        $result = $installer->createAdmin($name, $email, $password);

        if (! $result['ok']) {
            $errors[] = $result['message'];
        } else {
            $readiness = $installer->readiness();

            if (! $readiness['ok']) {
                $errors[] = 'Setup completed but readiness checks failed. Review the summary below.';
                $step = 'complete';
            } else {
                $installer->disableSetupRedirect();
                $_SESSION = [];
                session_destroy();
                header('Location: /login');
                exit;
            }
        }
    }

    if ($errors !== [] && $step !== 'complete') {
        $step = 'admin';
    }
}

$requirements = $installer->requirements();
$readiness = $step === 'complete' ? $installer->readiness() : null;

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Huddle setup</title>
    <style>
        :root { color-scheme: light; font-family: ui-sans-serif, system-ui, sans-serif; }
        body { margin: 0; background: #f4f4f5; color: #18181b; }
        .wrap { max-width: 42rem; margin: 0 auto; padding: 2rem 1rem 4rem; }
        .card { background: #fff; border: 1px solid #e4e4e7; border-radius: 0.75rem; padding: 1.5rem; box-shadow: 0 1px 2px rgb(0 0 0 / 0.04); }
        h1 { margin: 0 0 0.25rem; font-size: 1.75rem; }
        .muted { color: #71717a; font-size: 0.95rem; }
        .steps { display: flex; gap: 0.5rem; margin: 1.5rem 0; flex-wrap: wrap; }
        .step { padding: 0.35rem 0.75rem; border-radius: 999px; font-size: 0.8rem; background: #f4f4f5; color: #52525b; }
        .step.active { background: #287878; color: #fff; }
        .check { display: flex; justify-content: space-between; gap: 1rem; padding: 0.55rem 0; border-bottom: 1px solid #f4f4f5; font-size: 0.92rem; }
        .check:last-child { border-bottom: 0; }
        .ok { color: #15803d; font-weight: 600; }
        .bad { color: #b91c1c; font-weight: 600; }
        .warn { color: #a16207; font-weight: 600; }
        label { display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.35rem; }
        input, select { width: 100%; box-sizing: border-box; padding: 0.65rem 0.75rem; border: 1px solid #d4d4d8; border-radius: 0.5rem; font: inherit; }
        .field { margin-bottom: 1rem; }
        .grid { display: grid; gap: 1rem; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .btn { display: inline-flex; align-items: center; justify-content: center; border: 0; border-radius: 0.5rem; background: #287878; color: #fff; padding: 0.7rem 1rem; font-weight: 600; cursor: pointer; text-decoration: none; }
        .btn.secondary { background: #e4e4e7; color: #18181b; }
        .actions { display: flex; gap: 0.75rem; margin-top: 1.25rem; flex-wrap: wrap; }
        .alert { border-radius: 0.5rem; padding: 0.75rem 1rem; margin: 1rem 0; font-size: 0.92rem; }
        .alert.error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .alert.success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        .log { background: #fafafa; border: 1px solid #e4e4e7; border-radius: 0.5rem; padding: 0.75rem 1rem; font-size: 0.85rem; color: #3f3f46; }
        .log li { margin: 0.25rem 0; }
        .hidden { display: none; }
        @media (max-width: 640px) { .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <h1>Huddle setup</h1>
        <p class="muted">First-time installation wizard. Installation switches <code>.htaccess</code> to the installed version.</p>

        <div class="steps">
            <span class="step <?= in_array($step, ['requirements', 'database', 'admin', 'complete'], true) ? 'active' : '' ?>">1. Requirements</span>
            <span class="step <?= in_array($step, ['database', 'admin', 'complete'], true) ? 'active' : '' ?>">2. Database</span>
            <span class="step <?= in_array($step, ['admin', 'complete'], true) ? 'active' : '' ?>">3. Admin</span>
            <span class="step <?= $step === 'complete' ? 'active' : '' ?>">4. Finish</span>
        </div>

        <?php foreach ($errors as $error): ?>
            <div class="alert error"><?= e($error) ?></div>
        <?php endforeach; ?>

        <?php if ($step === 'requirements'): ?>
            <h2>Environment checks</h2>
            <?php foreach ($requirements['checks'] as $check): ?>
                <div class="check">
                    <span><?= e($check['label']) ?></span>
                    <span class="<?= $check['ok'] ? 'ok' : 'bad' ?>"><?= e($check['message']) ?></span>
                </div>
            <?php endforeach; ?>

            <div class="actions">
                <?php if ($requirements['ok']): ?>
                    <a class="btn" href="setup.php?step=database">Continue</a>
                <?php else: ?>
                    <span class="muted">Fix the issues above, then refresh this page.</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($step === 'database'): ?>
            <h2>Database configuration</h2>
            <p class="muted">Application URL detected as <strong><?= e($appUrl) ?></strong>.</p>

            <form method="post" action="setup.php">
                <input type="hidden" name="_token" value="<?= e($csrf) ?>">
                <input type="hidden" name="step" value="database">

                <div class="field">
                    <label for="db_connection">Database type</label>
                    <select name="db_connection" id="db_connection" onchange="toggleDbFields(this.value)">
                        <option value="sqlite" <?= ($_POST['db_connection'] ?? 'sqlite') === 'sqlite' ? 'selected' : '' ?>>SQLite (simplest)</option>
                        <option value="mysql" <?= ($_POST['db_connection'] ?? '') === 'mysql' ? 'selected' : '' ?>>MySQL / MariaDB</option>
                    </select>
                </div>

                <div id="mysql-fields" class="<?= ($_POST['db_connection'] ?? 'sqlite') === 'mysql' ? '' : 'hidden' ?>">
                    <div class="grid">
                        <div class="field">
                            <label for="db_host">Host</label>
                            <input id="db_host" name="db_host" value="<?= e($_POST['db_host'] ?? '127.0.0.1') ?>">
                        </div>
                        <div class="field">
                            <label for="db_port">Port</label>
                            <input id="db_port" name="db_port" value="<?= e($_POST['db_port'] ?? '3306') ?>">
                        </div>
                    </div>
                    <div class="field">
                        <label for="db_database">Database name</label>
                        <input id="db_database" name="db_database" value="<?= e($_POST['db_database'] ?? 'huddle') ?>">
                    </div>
                    <div class="grid">
                        <div class="field">
                            <label for="db_username">Username</label>
                            <input id="db_username" name="db_username" value="<?= e($_POST['db_username'] ?? '') ?>">
                        </div>
                        <div class="field">
                            <label for="db_password">Password</label>
                            <input id="db_password" type="password" name="db_password" value="">
                        </div>
                    </div>
                </div>

                <p class="muted">This step writes <code>.env</code>, generates <code>APP_KEY</code>, runs migrations, and seeds default roles and tags.</p>

                <div class="actions">
                    <a class="btn secondary" href="setup.php?step=requirements">Back</a>
                    <button class="btn" type="submit">Install database</button>
                </div>
            </form>
        <?php endif; ?>

        <?php if ($step === 'admin'): ?>
            <h2>Administrator account</h2>
            <p class="muted">Create the first administrator account. Any legacy <code>admin@huddle.skullfire.co.uk</code> user will be removed.</p>

            <?php if ($messages !== []): ?>
                <div class="alert success">
                    <ul class="log">
                        <?php foreach ($messages as $message): ?>
                            <li><?= e($message) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" action="setup.php">
                <input type="hidden" name="_token" value="<?= e($csrf) ?>">
                <input type="hidden" name="step" value="admin">

                <div class="field">
                    <label for="admin_name">Name</label>
                    <input id="admin_name" name="admin_name" value="<?= e($_POST['admin_name'] ?? '') ?>" required>
                </div>
                <div class="field">
                    <label for="admin_email">Email</label>
                    <input id="admin_email" type="email" name="admin_email" value="<?= e($_POST['admin_email'] ?? '') ?>" required>
                </div>
                <div class="grid">
                    <div class="field">
                        <label for="admin_password">Password</label>
                        <input id="admin_password" type="password" name="admin_password" required>
                    </div>
                    <div class="field">
                        <label for="admin_password_confirmation">Confirm password</label>
                        <input id="admin_password_confirmation" type="password" name="admin_password_confirmation" required>
                    </div>
                </div>

                <div class="actions">
                    <button class="btn" type="submit">Create admin and finish</button>
                </div>
            </form>
        <?php endif; ?>

        <?php if ($step === 'complete' && $readiness): ?>
            <h2>Readiness check</h2>
            <?php foreach ($readiness['checks'] as $check): ?>
                <div class="check">
                    <span><?= e($check['label']) ?></span>
                    <span class="<?= $check['ok'] ? 'ok' : (($check['required'] ?? true) ? 'bad' : 'warn') ?>"><?= e($check['message']) ?></span>
                </div>
            <?php endforeach; ?>

            <div class="actions">
                <?php if ($readiness['ok']): ?>
                    <?php $installer->disableSetupRedirect(); ?>
                    <a class="btn" href="/login">Go to login</a>
                <?php else: ?>
                    <span class="muted">Resolve the failed checks, then refresh or re-run setup.</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<script>
function toggleDbFields(connection) {
    document.getElementById('mysql-fields').classList.toggle('hidden', connection !== 'mysql');
}
</script>
</body>
</html>
