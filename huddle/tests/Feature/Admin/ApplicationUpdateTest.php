<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Index;
use App\Models\User;
use App\Services\ApplicationUpdateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class ApplicationUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_updates_tab(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('setTab', 'updates')
            ->assertSet('activeTab', 'updates')
            ->assertSee(__('Application updates'));
    }

    public function test_admin_can_check_for_updates(): void
    {
        Http::fake([
            'api.github.com/*' => Http::response([
                'tag_name' => 'v9.9.9',
                'html_url' => 'https://github.com/AllanGallop/Huddle/releases/tag/v9.9.9',
                'assets' => [
                    [
                        'name' => 'huddle-v9.9.9.zip',
                        'browser_download_url' => 'https://github.com/AllanGallop/Huddle/releases/download/v9.9.9/huddle-v9.9.9.zip',
                    ],
                ],
            ], 200),
        ]);

        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('setTab', 'updates')
            ->call('checkForUpdates')
            ->assertSet('latestReleaseTag', 'v9.9.9')
            ->assertSet('updateAvailable', true)
            ->assertSet('latestReleaseZipUrl', 'https://github.com/AllanGallop/Huddle/releases/download/v9.9.9/huddle-v9.9.9.zip');
    }

    public function test_admin_can_apply_database_update(): void
    {
        $admin = User::factory()->admin()->create();

        $component = Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('setTab', 'updates')
            ->call('applyDatabaseUpdate')
            ->assertHasNoErrors();

        $this->assertNotEmpty($component->get('updateOutput'));
    }

    public function test_non_admin_cannot_access_admin_updates(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.index'))
            ->assertForbidden();
    }

    public function test_service_detects_newer_semver_release(): void
    {
        $service = app(ApplicationUpdateService::class);

        $this->assertTrue($service->isNewerThanInstalled('v1.2.0', 'v1.1.0'));
        $this->assertFalse($service->isNewerThanInstalled('v1.1.0', 'v1.1.0'));
        $this->assertFalse($service->isNewerThanInstalled('v1.0.0', 'v1.1.0'));
    }
}
