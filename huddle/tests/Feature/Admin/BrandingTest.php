<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Index;
use App\Models\OrganizationSetting;
use App\Models\User;
use App\Services\BrandingService;
use App\Support\Branding;
use App\Support\DefaultBrandingGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class BrandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_branding_assets_are_generated(): void
    {
        app(BrandingService::class)->ensureDefaultAssets();

        $this->assertFileExists(public_path('images/branding/logo.svg'));
        $this->assertFileExists(public_path('images/branding/banner-light.svg'));
        $this->assertStringContainsString('Huddle', file_get_contents(public_path('images/branding/banner-light.svg')));
    }

    public function test_default_logo_generator_produces_huddle_mark(): void
    {
        $svg = DefaultBrandingGenerator::logoSvg();

        $this->assertStringContainsString(Branding::PRIMARY, $svg);
        $this->assertStringContainsString('<circle', $svg);
    }

    public function test_admin_can_upload_logo(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();

        $this->actingAs($admin);

        Livewire::test(Index::class)
            ->call('setTab', 'branding')
            ->set('logoUpload', UploadedFile::fake()->image('logo.png', 128, 128))
            ->call('saveBranding');

        $settings = OrganizationSetting::instance()->refresh();

        $this->assertNotNull($settings->logo_path);
        Storage::disk('public')->assertExists($settings->logo_path);
        $this->assertStringContainsString('/storage/', Branding::logoUrl());
    }

    public function test_admin_can_reset_branding_to_defaults(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $settings = OrganizationSetting::instance();
        $path = UploadedFile::fake()->image('logo.png')->store('branding', 'public');
        $settings->update(['logo_path' => $path]);

        $this->actingAs($admin);

        Livewire::test(Index::class)
            ->call('setTab', 'branding')
            ->call('resetBranding');

        $settings->refresh();

        $this->assertNull($settings->logo_path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_non_admin_cannot_access_admin_branding_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.index'))
            ->assertForbidden();
    }
}
