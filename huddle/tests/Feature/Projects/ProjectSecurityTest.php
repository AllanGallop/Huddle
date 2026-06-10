<?php

namespace Tests\Feature\Projects;

use App\Livewire\Projects\Index as ProjectsIndex;
use App\Livewire\Projects\Show as ProjectsShow;
use App\Models\Project;
use App\Models\ProjectImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ProjectSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function createImageFixture(Project $project): ProjectImage
    {
        Storage::fake('local');

        $path = 'projects/'.$project->id.'/fixture.jpg';
        Storage::disk('local')->put($path, 'fake-image-content');

        return ProjectImage::create([
            'project_id' => $project->id,
            'image_url' => $path,
        ]);
    }

    protected function createProjectFor(User $owner): Project
    {
        return Project::create([
            'name' => 'Secure project',
            'description' => 'Description',
            'created_by' => $owner->id,
            'leader_id' => $owner->id,
            'volunteer_required' => false,
            'project_status' => 'outstanding',
        ]);
    }

    public function test_guest_cannot_view_project_image(): void
    {
        $owner = User::factory()->create();
        $project = $this->createProjectFor($owner);
        $image = $this->createImageFixture($project);

        $this->get(route('projects.image', ['project' => $project, 'projectImage' => $image]))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_project_image(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $project = $this->createProjectFor($owner);
        $image = $this->createImageFixture($project);

        $this->actingAs($viewer)
            ->get(route('projects.image', ['project' => $project, 'projectImage' => $image]))
            ->assertOk();
    }

    public function test_non_manager_cannot_upload_project_image(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $other = User::factory()->create();
        $project = $this->createProjectFor($owner);

        Livewire::actingAs($other)
            ->test(ProjectsShow::class, ['project' => $project])
            ->set('photo', UploadedFile::fake()->create('intrusion.jpg', 100, 'image/jpeg'))
            ->call('uploadImage')
            ->assertForbidden();

        $this->assertDatabaseCount('project_images', 0);
    }

    public function test_member_created_project_assigns_self_as_leader(): void
    {
        $member = User::factory()->create();
        $otherLeader = User::factory()->create(['name' => 'Other Leader']);

        Livewire::actingAs($member)
            ->test(ProjectsIndex::class)
            ->set('name', 'Member project')
            ->set('description', 'Created by a member')
            ->set('project_status', 'draft')
            ->set('leader_id', $otherLeader->id)
            ->call('createProject')
            ->assertHasNoErrors();

        $project = Project::query()->where('name', 'Member project')->first();

        $this->assertNotNull($project);
        $this->assertSame($member->id, $project->leader_id);
        $this->assertSame($member->id, $project->created_by);
    }

    public function test_admin_can_assign_another_user_as_project_leader(): void
    {
        $admin = User::factory()->admin()->create();
        $leader = User::factory()->create(['name' => 'Assigned Leader']);

        Livewire::actingAs($admin)
            ->test(ProjectsIndex::class)
            ->set('name', 'Admin project')
            ->set('description', 'Created by an admin')
            ->set('project_status', 'draft')
            ->set('leader_id', $leader->id)
            ->call('createProject')
            ->assertHasNoErrors();

        $project = Project::query()->where('name', 'Admin project')->first();

        $this->assertNotNull($project);
        $this->assertSame($leader->id, $project->leader_id);
    }
}
