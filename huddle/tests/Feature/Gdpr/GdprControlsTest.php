<?php

namespace Tests\Feature\Gdpr;

use App\Livewire\Settings\Privacy;
use App\Models\Project;
use App\Models\ProjectComment;
use App\Models\Role;
use App\Models\User;
use App\Services\UserDataErasureService;
use App\Services\UserDataExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GdprControlsTest extends TestCase
{
    use RefreshDatabase;

    public function test_privacy_policy_page_is_public(): void
    {
        $this->get(route('privacy.show'))
            ->assertOk()
            ->assertSee('Privacy policy');
    }

    public function test_unaccepted_user_is_redirected_to_privacy_settings(): void
    {
        $user = User::factory()->create([
            'privacy_policy_accepted_at' => null,
            'privacy_policy_version' => null,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('privacy.edit'));
    }

    public function test_user_can_accept_privacy_policy(): void
    {
        $user = User::factory()->create([
            'privacy_policy_accepted_at' => null,
            'privacy_policy_version' => null,
        ]);

        Livewire::actingAs($user)
            ->test(Privacy::class)
            ->set('accept_policy', true)
            ->call('acceptPrivacyPolicy')
            ->assertRedirect(route('dashboard'));

        $user->refresh();

        $this->assertTrue($user->hasAcceptedPrivacyPolicy());
    }

    public function test_user_can_export_personal_data(): void
    {
        $user = User::factory()->create([
            'name' => 'Export Me',
            'email' => 'export@example.com',
        ]);

        $this->actingAs($user)
            ->get(route('user-data.export'))
            ->assertOk()
            ->assertHeader('content-type', 'application/json')
            ->assertDownload('huddle-data-export-export-me-'.now()->format('Y-m-d').'.json');
    }

    public function test_export_service_includes_profile_and_activity(): void
    {
        $user = User::factory()->create(['name' => 'Alex Member']);
        $leader = User::factory()->create();

        $project = Project::create([
            'name' => 'Test project',
            'description' => 'Description',
            'created_by' => $leader->id,
            'leader_id' => $leader->id,
            'volunteer_required' => false,
            'project_status' => 'outstanding',
        ]);

        ProjectComment::create([
            'project_id' => $project->id,
            'user_id' => $user->id,
            'parent_comment_id' => null,
            'comment' => 'My GDPR comment',
        ]);

        $export = app(UserDataExportService::class)->export($user->fresh());

        $this->assertSame('Alex Member', $export['profile']['name']);
        $this->assertSame('My GDPR comment', $export['project_comments'][0]['comment']);
    }

    public function test_erasure_removes_user_and_reassigns_owned_records(): void
    {
        $memberRoleId = Role::query()->where('name', 'member')->value('id');
        $user = User::factory()->create(['role_id' => $memberRoleId]);
        $other = User::factory()->create();

        $project = Project::create([
            'name' => 'Owned project',
            'description' => 'Description',
            'created_by' => $user->id,
            'leader_id' => $user->id,
            'volunteer_required' => false,
            'project_status' => 'outstanding',
        ]);

        ProjectComment::create([
            'project_id' => $project->id,
            'user_id' => $user->id,
            'parent_comment_id' => null,
            'comment' => 'To be deleted',
        ]);

        app(UserDataErasureService::class)->erase($user);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('project_comments', ['comment' => 'To be deleted']);

        $placeholder = User::query()->where('email', config('gdpr.placeholder_email'))->first();
        $this->assertNotNull($placeholder);

        $project->refresh();
        $this->assertSame($placeholder->id, $project->leader_id);
        $this->assertSame($placeholder->id, $project->created_by);
    }

    public function test_self_service_delete_uses_erasure_service(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('settings.delete-user-form')
            ->set('password', 'password')
            ->call('deleteUser')
            ->assertRedirect('/');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
