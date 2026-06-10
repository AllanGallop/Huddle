<?php

namespace Tests\Feature\Setup;

use App\Models\Role;
use App\Models\User;
use App\Support\Installer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InstallerTest extends TestCase
{
    use RefreshDatabase;

    public function test_requirements_pass_in_test_environment(): void
    {
        $result = Installer::make()->requirements();

        $this->assertTrue($result['ok']);
        $this->assertNotEmpty($result['checks']);
    }

    public function test_sqlite_connection_can_be_tested(): void
    {
        $path = database_path('installer-test.sqlite');

        if (is_file($path)) {
            unlink($path);
        }

        $result = Installer::make()->testDatabaseConnection([
            'connection' => 'sqlite',
            'database' => $path,
        ]);

        $this->assertTrue($result['ok'], $result['message']);
        $this->assertFileExists($path);

        unlink($path);
    }

    public function test_create_admin_removes_legacy_default_admin(): void
    {
        $installer = Installer::make();
        $adminRoleId = Role::query()->where('name', 'admin')->value('id');

        $legacy = new User([
            'name' => 'Legacy Admin',
            'email' => 'admin@huddle.skullfire.co.uk',
            'password' => Hash::make('password'),
        ]);
        $legacy->role_id = $adminRoleId;
        $legacy->save();

        $result = $installer->createAdmin('New Admin', 'admin@example.com', 'secure-password-12');

        $this->assertTrue($result['ok'], $result['message']);
        $this->assertDatabaseMissing('users', ['email' => 'admin@huddle.skullfire.co.uk']);
        $this->assertDatabaseHas('users', ['email' => 'admin@example.com']);
    }

    public function test_readiness_passes_with_admin_user(): void
    {
        User::factory()->admin()->create();

        $result = Installer::make()->readiness();

        $this->assertTrue($result['ok']);
    }

    public function test_is_installed_when_admin_exists(): void
    {
        $this->assertFalse(Installer::make()->isInstalled());

        User::factory()->admin()->create();

        $this->assertTrue(Installer::make()->isInstalled());
    }

    public function test_disable_setup_redirect_switches_htaccess_files(): void
    {
        $public = public_path();
        $originalActive = file_get_contents($public.'/.htaccess');
        $originalSetup = is_file($public.'/.htaccess.setup') ? file_get_contents($public.'/.htaccess.setup') : null;
        $originalInstalled = is_file($public.'/.htaccess.installed') ? file_get_contents($public.'/.htaccess.installed') : null;

        copy($public.'/.htaccess.setup', $public.'/.htaccess');

        $installer = new Installer(dirname(__DIR__, 3));

        $this->assertTrue($installer->isSetupRedirectEnabled());
        $this->assertTrue($installer->disableSetupRedirect());
        $this->assertFalse($installer->isSetupRedirectEnabled());
        $this->assertStringNotContainsString('setup.php', file_get_contents($public.'/.htaccess'));
        $this->assertFileExists($public.'/.htaccess.setup');
        $this->assertFileExists($public.'/.htaccess.installed');
        $this->assertTrue($installer->disableSetupRedirect());

        file_put_contents($public.'/.htaccess', $originalActive);

        if ($originalSetup !== null) {
            file_put_contents($public.'/.htaccess.setup', $originalSetup);
        }

        if ($originalInstalled !== null) {
            file_put_contents($public.'/.htaccess.installed', $originalInstalled);
        }
    }
}
