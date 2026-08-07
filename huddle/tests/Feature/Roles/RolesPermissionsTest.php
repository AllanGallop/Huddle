<?php

namespace Tests\Feature\Roles;

use App\Livewire\Admin\Index as AdminIndex;
use App\Models\Event;
use App\Models\Permission;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RolesPermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_permission_union_across_multiple_roles(): void
    {
        $editAny = Permission::query()->where('slug', Permission::PROJECTS_EDIT_ANY)->firstOrFail();
        $wikiEdit = Permission::query()->where('slug', Permission::WIKI_EDIT)->firstOrFail();

        $roleA = Role::query()->create(['name' => 'Editors', 'description' => 'Project editors', 'is_system' => false]);
        $roleA->permissions()->attach($editAny);

        $roleB = Role::query()->create(['name' => 'Writers', 'description' => 'Wiki writers', 'is_system' => false]);
        $roleB->permissions()->attach($wikiEdit);

        $user = User::factory()->create();
        $user->roles()->sync([$roleA->id, $roleB->id]);

        $this->assertTrue($user->fresh()->hasPermission(Permission::PROJECTS_EDIT_ANY));
        $this->assertTrue($user->fresh()->hasPermission(Permission::WIKI_EDIT));
        $this->assertFalse($user->fresh()->hasPermission(Permission::FORMS_MANAGE));
    }

    public function test_admin_bypasses_all_gates(): void
    {
        $admin = User::factory()->admin()->create();

        foreach (Permission::slugs() as $slug) {
            $this->assertTrue($admin->can($slug), "Admin should pass gate {$slug}");
        }
    }

    public function test_edit_any_project_permission_allows_updating_foreign_project(): void
    {
        $owner = User::factory()->create();
        $editorRole = Role::query()->create(['name' => 'Project Editor', 'is_system' => false]);
        $editorRole->permissions()->attach(
            Permission::query()->where('slug', Permission::PROJECTS_EDIT_ANY)->value('id')
        );
        $editor = User::factory()->create();
        $editor->roles()->sync([$editorRole->id]);

        $project = Project::query()->create([
            'name' => 'Owned project',
            'description' => 'Desc',
            'created_by' => $owner->id,
            'leader_id' => $owner->id,
            'project_status' => 'draft',
            'volunteer_required' => false,
        ]);

        $this->assertTrue($editor->can('update', $project));
        $this->assertFalse($editor->can('delete', $project));
    }

    public function test_delete_any_event_permission_allows_deleting_foreign_event(): void
    {
        $owner = User::factory()->create();
        $deleterRole = Role::query()->create(['name' => 'Event Deleter', 'is_system' => false]);
        $deleterRole->permissions()->attach(
            Permission::query()->where('slug', Permission::EVENTS_DELETE_ANY)->value('id')
        );
        $deleter = User::factory()->create();
        $deleter->roles()->sync([$deleterRole->id]);

        $event = Event::query()->create([
            'name' => 'Owned event',
            'description' => 'Desc',
            'location' => 'Hall',
            'start_time' => now()->addDay(),
            'end_time' => now()->addDay()->addHours(2),
            'created_by' => $owner->id,
            'event_status' => 'published',
            'event_type' => 'public',
            'volunteer_required' => false,
        ]);

        $this->assertFalse($deleter->can('update', $event));
        $this->assertTrue($deleter->can('delete', $event));
    }

    public function test_member_can_view_public_accreditations_page(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)
            ->get(route('accreditations.index'))
            ->assertOk();
    }

    public function test_admin_can_create_role_with_permissions(): void
    {
        $admin = User::factory()->admin()->create();
        $permissionIds = Permission::query()->whereIn('slug', [
            Permission::WIKI_EDIT,
            Permission::FORMS_MANAGE,
        ])->pluck('id')->all();

        Livewire::actingAs($admin)
            ->test(AdminIndex::class)
            ->call('setTab', 'roles')
            ->call('openCreateRoleModal')
            ->set('role_name', 'Content Lead')
            ->set('role_description', 'Wiki and forms')
            ->set('assignedPermissionIds', $permissionIds)
            ->call('saveRole')
            ->assertHasNoErrors();

        $role = Role::query()->where('name', 'Content Lead')->first();
        $this->assertNotNull($role);
        $this->assertEqualsCanonicalizing(
            [Permission::WIKI_EDIT, Permission::FORMS_MANAGE],
            $role->permissions()->pluck('slug')->all()
        );
    }

    public function test_system_role_cannot_be_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $memberRole = Role::query()->where('name', 'member')->firstOrFail();

        Livewire::actingAs($admin)
            ->test(AdminIndex::class)
            ->call('deleteRole', $memberRole->id)
            ->assertHasErrors('role');

        $this->assertDatabaseHas('roles', ['id' => $memberRole->id]);
    }
}
